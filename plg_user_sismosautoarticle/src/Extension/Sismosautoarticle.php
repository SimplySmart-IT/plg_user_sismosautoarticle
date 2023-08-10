<?php /**
* @package     Joomla.Plugins
* @subpackage  User.Sismosautoarticle
*
* @author      Martina Scholz <martina@simplysmart-it.de>
* @copyright   (C) 2023 Martina Scholz, SimplySmart-IT <https://simplysmart-it.de>
* @license     GNU General Public License version 3 or later; see LICENSE.txt
* @link        https://simplysmart-it.de
*/

namespace Sismos\Plugin\User\Sismosautoarticle\Extension;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\String\StringHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\Log\Log;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

final class Sismosautoarticle extends CMSPlugin implements SubscriberInterface
{
	/**
	 * @var    \Joomla\CMS\Application\CMSApplication
	 *
	 * @since  4.0.0
	 */
	protected $app;

	/**
	 * @var    \Joomla\Database\DatabaseDriver
	 *
	 * @since  4.0.0
	 */
	protected $db;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 * Note this is only available in Joomla 3.1 and higher.
	 * If you want to support 3.0 series you must override the constructor
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor
	 *
	 * @param   DispatcherInterface  &$subject  The object to observe
	 * @param   array                $config    An optional associative array of configuration settings.
	 *                                          Recognized key values include 'name', 'group', 'params', 'language'
	 *                                         (this list is not meant to be comprehensive).
	 *
	 * @since   1.0.0
	 */
	public function __construct(&$subject, $config = [])
	{
		parent::__construct($subject, $config);

		// Define the logger.
		Log::addLogger(['text_file' => 'plg_user_sismosautoarticle.php'], Log::ALL, ['plg_user_sismosautoarticle']);
	}

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onUserAfterSave'   => 'addArticleOnActivation',
		];
	}

	/**
	 * Add Article on user activation if not exists.
	 *
	 * @param   \Joomla\Event\Event $event
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 *
	 */
	public function addArticleOnActivation(\Joomla\Event\Event $event)
	{
		/** @var array    $user     Holds the new user data.*/
		/** @var boolean  $isnew    True if a new user is stored. */
		/** @var boolean  $success  True if user was successfully stored in the database. */
		/** @var string   $msg      Message. */

		[$user, $isnew, $success, $msg] = $event->getArguments();

		if (!$success) {
			return;
		}

		if (!is_array($user) || empty($user) || empty((int) $user['id'])) {
			return;
		}

		$usergroupsFilter = $this->params->get('usergroup', []);

		$check = array_intersect($usergroupsFilter,$user['groups']);

		if (is_array($usergroupsFilter) && !empty($usergroupsFilter)) {
			foreach($usergroupsFilter as &$group) {
				$group = (int) $group;
			}
			if(empty(array_intersect($usergroupsFilter,$user['groups']))) {
				return;
			}
		}

		if ($user['activation'] || $user['block']) {
			return;
		}

		if ($this->userArticleExists($user)) {
			return;
		}

		$newArticleId = $this->createUserArticle($user);

		return;
	}

	/**
	 * Check if article for user already exists
	 *
	 * @param   array $user
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	private function userArticleExists($user)
	{
		/** @var \Joomla\Component\Content\Site\Model\ArticlesModel $articlesModel */
		$articlesModel = $this->app->bootComponent('com_content')->getMVCFactory()->createModel('Articles', 'Administrator', ['ignore_request' => true]);
		// This module does not use tags data
		$articlesModel->setState('load_tags', false);
		// Category filter
		$articlesModel->setState('filter.category_id', $this->params->get('catid', 2));
		// User filter
		$articlesModel->setState('filter.author_id', $user['id']);

		$existsUserArticle = $articlesModel->getTotal();

		return boolval($existsUserArticle);
	}

	/**
	 * Create article for user
	 *
	 * @param   array $user
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function createUserArticle($user)
	{
		$mvcFactory = $this->app->bootComponent('com_content')->getMVCFactory();
		/** @var Joomla\Component\Content\Administrator\Model $articleModel */
		$articleModel = $mvcFactory->createModel('Article', 'Administrator', ['ignore_request' => true]);

		// Get some metadata.
		$access = (int) $this->app->get('access', 1);
		// Set values from language strings.
		$title                = ($t = trim($this->params->get('title', ''))) ? Text::_($t) . ' - ' . $user['name'] : $user['name'];
		$alias                = ApplicationHelper::stringURLSafe($user['name']);
		// Set unicodeslugs if alias is empty
		if (trim(str_replace('-', '', $alias) == '')) {
			$unicode          = $this->app->set('unicodeslugs', 1);
			$article['alias'] = ApplicationHelper::stringURLSafe($user['name']);
			$this->app->set('unicodeslugs', $unicode);
		}
		
		$article = [];
		$article['title']     					= $title;
		$article['articletext'] 	 			= $this->params->get('articletext', '');
		$article['catid']			 			= $this->params->get('catid', 2);
		$article['created_by']       			= $user['id'];
		$article['alias']            			= $this->generateAlias($alias, $article['catid']);
		$article['state']  			 			= $this->params->get('state', 1);
		$article['access'] 			 			= $this->params->get('access', $access);
		$article['attribs']['article_layout'] 	= $this->params->get('article_layout', "");
		$article['metadata']['robots'] 			= $this->params->get('robots', "");

		

		// Set values which are always the same.
		$article['id']               = 0;
		$article['ordering']         = 0;
		$article['language']        = "*";
		$article['associations']    = [];
		$article['metakey']         = '';
		$article['metadesc']        = '';

		if (!isset($article['images'])) {
			$article['images']  = '';
		}

		if (!$articleModel->save($article)) {
			$this->app->getLanguage()->load('com_content', JPATH_ADMINISTRATOR);
			$this->log(Text::sprintf('PLG_USER_SISMOSAUTOARTICLE_CREATE_FAILED', "ID" . $user['id'], Text::_($articleModel->getError())), Log::ERROR);
			$this->sendErrorMessage(Text::sprintf('PLG_USER_SISMOSAUTOARTICLE_CREATE_FAILED', "", ""), Text::sprintf('PLG_USER_SISMOSAUTOARTICLE_CREATE_FAILED', "ID" . $user['id'], Text::_($articleModel->getError())));
			if ($this->app->isClient('administrator') && $this->app->getIdentity()->authorise('core.admin')) {
				$this->app->enqueueMessage(Text::sprintf('PLG_USER_SISMOSAUTOARTICLE_CREATE_FAILED', "ID" . $user['id'], Text::_($articleModel->getError())), 'error');
			}
		}

		return;
	}

	/**
	 * Method to change the name & alias if alias is already in use
	 *
	 * @param   string   $alias       The alias.
	 * @param   integer  $categoryId  Category identifier
	 *
	 * @return  string  the modified alias.
	 *
	 * @since   1.0.0
	 */
	private function generateAlias($alias, $categoryId)
	{
		/** @var Joomla\Component\Content\Administrator\Table\ArticleTable $table */
		$table = $this->app->bootComponent('com_content')->getMVCFactory()->createTable('Article', 'Administrator', ['dbo' => $this->db]);

		while ($table->load(['alias' => $alias, 'catid' => $categoryId])) {
			$alias = StringHelper::increment($alias, 'dash');
		}

		return $alias;
	}

	/**
	 * Method to send an error message to Admin.
	 *
	 * @param   string    $subject   The subject for the message.
	 * @param   string	  $message   The message text
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	private function sendErrorMessage($subject, $message)
	{

		// Messaging to admin on Error

		// Push a notification to the site's super users, send email to user fails so the below message goes out
		/** @var MessageModel $messageModel */
		$messageModel = $this->app->bootComponent('com_messages')->getMVCFactory()->createModel('Message', 'Administrator');

		$messageModel->notifySuperUsers(
			$subject,
			$message
		);
	}

	/**
	 * Log helper function
	 *
	 * @param   string    $msg   	The log message text
	 * @param   string	  $type		The log message type
	 *
	 * @return	void
	 */
	private function log($msg, $type)
	{
		if ($this->params->get('log_on', 1)) {
			Log::add($msg, $type, 'plg_user_sismosautoarticle');
		}
	}
}
