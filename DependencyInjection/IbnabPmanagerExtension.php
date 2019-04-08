<?php

namespace Ibnab\Bundle\PmanagerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class IbnabPmanagerExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('ibnab_pmanager.file', $config['file']);
        $container->setParameter('ibnab_pmanager.class', $config['class']);
        $container->setParameter('ibnab_pmanager.tcpdf', $config['tcpdf']);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('processor.yml');
        $loader->load('form.yml');
        $loader->load('actions.yml');
        
        //$loader->load('search.yml');
        $container->prependExtensionConfig($this->getAlias(), array_intersect_key($config, array_flip(['settings'])));
    }
    /**
     * Get alias
     *
     * @return string
     */
    public function getAlias()
    {
        return 'ibnab_pmanager';
    }

}
