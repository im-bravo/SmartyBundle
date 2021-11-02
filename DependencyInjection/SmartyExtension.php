<?php
/**
 * This file is part of NoiseLabs-SmartyBundle
 *
 * NoiseLabs-SmartyBundle is free software; you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * NoiseLabs-SmartyBundle is distributed in the hope that it will be
 * useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with NoiseLabs-SmartyBundle; if not, see
 * <http://www.gnu.org/licenses/>.
 *
 * Copyright (C) 2011-2016 Vítor Brandão
 *
 * @category    NoiseLabs
 * @package     SmartyBundle
 * @copyright   (C) 2011-2016 Vítor Brandão <vitor@noiselabs.org>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL-3
 * @link        http://www.noiselabs.org
 */

namespace NoiseLabs\Bundle\SmartyBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * SmartyExtension.
 *
 * This is the class that loads and manages SmartyBundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @author Vítor Brandão <vitor@noiselabs.org>
 */
class SmartyExtension extends Extension
{
    /**
     * Responds to the smarty configuration parameter.
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('smarty.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $engineDefinition = $container->getDefinition('templating.engine.smarty');

        if (!empty($config['globals'])) {
            foreach ($config['globals'] as $key => $global) {
                if (isset($global['type']) && 'service' === $global['type']) {
                    $engineDefinition->addMethodCall('addGlobal', [$key, new Reference($global['id'])]);
                } else {
                    $engineDefinition->addMethodCall('addGlobal', [$key, $global['value']]);
                }
            }
        }

        $container->setParameter('smarty.options', $config['options']);

        /**
         * @note Caching of Smarty classes was causing issues because of the
         * include_once directives used in Smarty.class.php so this
         * feature is disabled.
         *
         * <code>
        $this->addClassesToCompile(array(
            'Smarty',
            'Smarty_Internal_Data',
            'Smarty_Internal_Templatebase',
            'Smarty_Internal_Template',
            'Smarty_Resource',
            'Smarty_Internal_Resource_File',
            'Smarty_Cacheresource',
            'Smarty_Internal_Cacheresource_File',
        ));
        * </code>
        */
    }

    public function getAlias()
    {
        return 'smarty';
    }
}
