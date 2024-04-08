<?php

declare(strict_types=1);

namespace Sparklink\EmailErrorsBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class EmailErrorsBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('enabled')->defaultTrue()->end()
                ->scalarNode('mailer_service')->defaultValue('mailer.mailer')->end()
                ->scalarNode('from')->isRequired()->end()
                ->scalarNode('to')->isRequired()->end()
                ->scalarNode('subject')->defaultValue('Exception')->end()
                ->arrayNode('ignored_exception_classes')
                    ->prototype('scalar')
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->setParameter('email_errors.from', $config['from']);
        $containerBuilder->setParameter('email_errors.to', $config['to']);
        $containerBuilder->setParameter('email_errors.subject', $config['subject']);
        $containerBuilder->setParameter('email_errors.ignored_exception_classes', $config['ignored_exception_classes']);

        $containerConfigurator->import('../config/services.yaml');

        $containerConfigurator->services()
            ->get('email_errors.exception_listener')
            ->arg(0, new Reference($config['mailer_service']))
        ;
    }
}
