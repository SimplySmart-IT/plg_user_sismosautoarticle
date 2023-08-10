<?php
/**
 * @package     Joomla.Plugin
 * @subpackage	User.Sismosautoarticle
 *
 * @author     Martina Scholz <martina@simplysmart-it.de>
 * @copyright  (C) 2023 Martina Scholz, SimplySmart-IT <https://simplysmart-it.de>
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html; see LICENSE.txt
 * @link       https://simplysmart-it.de
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Language\Text;

class PlgUserSismosautoarticleInstallerScript extends InstallerScript
{
	/**
	 * Minimum supported Joomla! version
	 *
	 * @var    string
	 * @since  1.1
	 */
	protected $minimumJoomla = '4.0.0';

	/**
	 * Minimum supported PHP version
	 *
	 * @var    string
	 * @since  1.1
	 */
	protected $minimumPhp = '7.4.0';

	/**
	 * Function called after extension installation/update/removal procedure commences.
	 *
	 * @param   string            $type     The type of change (install or discover_install, update, uninstall)
	 * @param   InstallerAdapter  $adapter  The adapter calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   4.2.0
	 */
	public function postflight(string $type, InstallerAdapter $adapter)
	{
		if ($type == 'uninstall') {
			return;
		}

		$element = 'sismosautoarticle';

		/** @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('DatabaseDriver');

		 // Construct the query
		 $query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = "plugin"')
			->where($db->quoteName('element') . ' = :elm')
			->bind(':elm', $element);

		// Setup the query
		$db->setQuery($query);

		$id = $db->loadResult();

		$lang = Factory::getApplication()->getLanguage();
		$lang->load('plg_user_' . $element, JPATH_ADMINISTRATOR);

		Factory::getApplication()->enqueueMessage(Text::sprintf('PLG_USER_SISMOSAUTOARTICLE_POSTINSTALL_MSG', $id));
	}
}
