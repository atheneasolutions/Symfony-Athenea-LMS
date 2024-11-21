<?php

namespace Athenea\LMS\DependencyInjection;

use Athenea\LMS\Service\LmsAuthService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class AtheneaLaMevaSalutExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');
        //$loader->load('monolog.yaml'); // Load monolog configuration

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('athenea_lms.app_url', $config['app_url']);
        $container->setParameter('athenea_lms.app_logo', $config['app_logo']);
        $container->setParameter('athenea_lms.app_name', $config['app_name']);

        
       // Resolve paths relative to the bundle
       $bundleDir = __DIR__ . '/../..'; // Adjust as necessary
       $devPrivateKeyPath = $bundleDir . '/config/jwt/dev_env/private.pem';
       $devPublicKeyPath = $bundleDir . '/config/jwt/dev_env/public.pem';

       // Define parameters for keys
       $container->setParameter('athenea.lms.dev_private_key_path', $devPrivateKeyPath);
       $container->setParameter('athenea.lms.dev_public_key_path', $devPublicKeyPath);
       $container->setParameter('athenea.lms.lms_public_key', $config['lms_public_key']);
       $container->setParameter('athenea.lms.app_public_key', $config['app_public_key']);
       $container->setParameter('athenea.lms.app_private_key', $config['app_private_key']);
        // $definition = $container->getDefinition("athenea.monolog.activation_strategy.param_based_activation");
        // $definition->replaceArgument('$enabled', $config['send_log_mails']);
    }

    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasExtension('doctrine_mongodb')) {
            $container->prependExtensionConfig('doctrine_mongodb', [
                'document_managers' => [
                    'default' => [
                        'mappings' => [
                            'athenea_la_meva_salut' => [
                                'is_bundle' => false,
                                'type' => 'attribute',
                                'dir' => __DIR__ . '/../Document',
                                'prefix' => 'Athenea\LMS\Document',
                            ],
                        ],
                    ],
                ],
            ]);
        }
    }

    public function getAlias(): string
    {
        return 'athenea_lms';
    }

}
