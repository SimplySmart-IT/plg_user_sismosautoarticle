<?php
/**
* @package     Joomla.Plugins
* @subpackage  User.Sismosautoarticle
*
* @author      Martina Scholz <martina@simplysmart-it.de>
* @copyright   (C) 2023 Martina Scholz, SimplySmart-IT <https://simplysmart-it.de>
* @license     GNU General Public License version 3 or later; see LICENSE.txt
* @link        https://simplysmart-it.de
*/

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Sismos\Plugin\User\Sismosautoarticle\Extension\Sismosautoarticle;

return new class () implements ServiceProviderInterface {
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   4.3.0
	 */
	public function register(Container $container)
	{
		$container->set(
			PluginInterface::class,
			function (Container $container) {
				$dispatcher = $container->get(DispatcherInterface::class);
				$plugin     =  new Sismosautoarticle(
					$dispatcher,
					(array) PluginHelper::getPlugin('user', 'sismosautoarticle')
				);
				$plugin->setApplication(Factory::getApplication());

				return $plugin;
			}
		);
	}
};
