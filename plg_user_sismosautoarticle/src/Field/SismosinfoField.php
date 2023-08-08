<?php /**
* @package     SISMOpenSource
* @subpackage  Fields.sismosinfo
*
* @author      Martina Scholz <martina@simplysmart-it.de>
* @copyright   (C) 2023 Martina Scholz, SimplySmart-IT <https://simplysmart-it.de>
* @license     GNU General Public License version 2 or later; see LICENSE.txt
* @link        https://simplysmart-it.de
*/

namespace Sismos\Plugin\User\Sismosautoarticle\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Platform.
 *
 * Provides a pop up date picker linked to a button.
 * Optionally may be filtered to use user's or server's time zone.
 *
 * @since  1.0.3
 */
class SismosinfoField extends FormField
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.3
	 */
	protected $type = 'sismosinfo';

	/**
	 * Method to get the field input markup for a spacer.
	 * The spacer does not have accept input.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.7.0
	 */
	protected function getInput()
	{
		return ' ';
	}

	/**
	 * Method to get the field label markup for a spacer.
	 * Use the label text or name from the XML element as the spacer or
	 * Use a hr="true" to automatically generate plain hr markup
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   1.7.0
	 */
	protected function getLabel()
	{
		$html = [];

		$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
		$wa->registerAndUseStyle('sismos.info', 'plg_user_sismosautoarticle/sismosinfo.css')
			->useStyle('fontawesome');
		$html[] = '<figure class="snip1578">
					<img src="' . Uri::root() . '/media/plg_user_sismosautoarticle/images/sismos_pic1.svg" alt="Bitmoji SimplySmart-OpenSource" width="auto" height="120"/>
					<figcaption>						
						<p>Made With <i class="fas fa-heart"></i></p>
						<p>SimplySmart <i class="fas fa-magic"></i> OpenSource</p>
						<div class="icons">
							<a href="www.linkedin.com/in/scholz-martina" title="LinkedIn" target="_blank"><i class="fab fa-linkedin"></i></a>
							<a href="https://github.com/SimplySmart-IT" title="Github" target="_blank"> <i class="fab fa-github-square"></i></a>
							<a href="https://simplysmart-it.de" title="simplysmart-it.de" target="_blank"><i class="fa fa-globe"></i></a>
						</div>
					</figcaption>
				</figure>';

		$html[] = '<div class="sismit-logo">
					<img src="' . Uri::root() . '/media/plg_user_sismosautoarticle/images/sismit_logo.svg" alt="Bitmoji SimplySmart-OpenSource" width="100" height="auto"/>
				</div>';

		return implode('', $html);
	}

	/**
	 * Method to get the field title.
	 *
	 * @return  string  The field title.
	 *
	 * @since   1.7.0
	 */
	protected function getTitle()
	{
		return $this->getLabel();
	}

	/**
	 * Method to get a control group with label and input.
	 *
	 * @param   array  $options  Options to be passed into the rendering of the field
	 *
	 * @return  string  A string containing the html for the control group
	 *
	 * @since   3.7.3
	 */
	public function renderField($options = [])
	{
		$options['class'] = empty($options['class']) ? 'field-sismosinfo' : $options['class'] . ' field-sismosinfo';

		return parent::renderField($options);
	}
}
