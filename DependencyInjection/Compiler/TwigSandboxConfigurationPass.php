<?php

namespace Ibnab\Bundle\PmanagerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TwigSandboxConfigurationPass implements CompilerPassInterface {

    const IBNAB_PMANAGER_SANDBOX_SECURITY_POLICY_SERVICE_KEY = 'ibnab_pmanager.twig.security_policy';
    const IBNAB_TEMPLATE_RENDERER_SERVICE_KEY = 'ibnab_pmanager.pdftemplate_renderer';
    const CONFIG_EXTENSION_SERVICE_KEY = 'oro_config.twig.config_extension';
    const EMAIL_EXTENSION_SERVICE_KEY = 'oro_email.twig.extension.email';
    const FORMATTER_EXTENSION_SERVICE_KEY = 'oro_ui.twig.extension.formatter';
    const DATE_FORMAT_EXTENSION_SERVICE_KEY = 'oro_locale.twig.date_time';
    const NAME_FORMAT_EXTENSION_SERVICE_KEY = 'oro_entity.twig.extension.entity';
    const INTL_EXTENSION_SERVICE_KEY = 'twig.extension.intl';
    const LOCALE_ADDRESS = 'oro_locale.twig.address';
    const DATETIME_ORGANIZATION_FORMAT_EXTENSION_SERVICE_KEY = 'oro_locale.twig.date_time_organization';
    const NUMBER_EXTENSION_SERVICE_KEY = 'oro_locale.twig.number';
    const HTML_Tag_EXTENSION_SERVICE_KEY = 'oro_ui.twig.html_tag';
    const CURRENCY_SERVICE_KEY = 'oro_currency.twig.currency';
    const ROUTING_EXTENSION_SERVICE_KEY = 'twig.extension.routing';

    /**
     * {@inheritDoc}
     */
    protected function getFilters() {
        return [
            'oro_format_address',
            'oro_format_date',
            'oro_format_time',
            'oro_format_datetime',
            'oro_format_datetime_organization',
            'oro_format_name',
            'date',
            'oro_format_currency',
            'oro_html_sanitize'
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getFunctions() {
        return [
            'oro_order_shipping_method_label',
            'get_payment_methods',
            'get_payment_status_label',
            'get_payment_status',
            'line_items_discounts',
            'get_payment_status',
            'get_payment_status_label',            
            'get_payment_term',
            'get_payment_method_label',
            'oro_payment_method_config_template',
            'getlabel'
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions() {
        return [
            self::LOCALE_ADDRESS,
            self::DATE_FORMAT_EXTENSION_SERVICE_KEY,
            self::NAME_FORMAT_EXTENSION_SERVICE_KEY,
            self::NUMBER_EXTENSION_SERVICE_KEY,
            self::INTL_EXTENSION_SERVICE_KEY, // Register Intl twig extension required for our date format extension
            self::DATETIME_ORGANIZATION_FORMAT_EXTENSION_SERVICE_KEY,
            self::HTML_Tag_EXTENSION_SERVICE_KEY,
            'oro_order.twig.order_shipping',
            'oro_payment.twig.payment_method_extension',
            'oro_payment.twig.payment_status_extension',
            'oro_promotion.twig.extension.discounts_information',
            'oro_payment.twig.payment_status_extension',
            'oro_payment_term.twig.payment_term_extension',
            'oro_payment.twig.payment_method_extension'
        ];
    }

    /**
     * Register functions
     *
     * @param ContainerBuilder $container
     */
    private function registerFunctions(ContainerBuilder $container) {
        $functions = $this->getFunctions();
        if ($functions) {
            $this->registerArgument($container, 4, $functions);
        }
    }

    /**
     * Register filters
     *
     * @param ContainerBuilder $container
     */
    private function registerFilters(ContainerBuilder $container) {
        $filters = $this->getFilters();
        if ($filters) {
            $this->registerArgument($container, 1, $filters);
        }
    }

    /**
     * Register a specific argument
     *
     * @param ContainerBuilder $container
     * @param int $argumentIndex
     * @param array $argument
     */
    private function registerArgument(ContainerBuilder $container, $argumentIndex, $argument) {
        $securityPolicyDef = $container->getDefinition(self::IBNAB_PMANAGER_SANDBOX_SECURITY_POLICY_SERVICE_KEY);
        $argument = array_merge(
                $securityPolicyDef->getArgument($argumentIndex), $argument
        );
        $securityPolicyDef->replaceArgument($argumentIndex, $argument);
    }

    /**
     * Register a twig extensions
     *
     * @param ContainerBuilder $container
     */
    private function registerTwigExtensions(ContainerBuilder $container) {
        $rendererDef = $container->getDefinition(self::IBNAB_TEMPLATE_RENDERER_SERVICE_KEY);
        $extensions = $this->getExtensions();
        foreach ($extensions as $extension) {
            $rendererDef->addMethodCall('addExtension', [new Reference($extension)]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container) {
        if ($container->hasDefinition(self::IBNAB_PMANAGER_SANDBOX_SECURITY_POLICY_SERVICE_KEY) && $container->hasDefinition(self::IBNAB_TEMPLATE_RENDERER_SERVICE_KEY)
        ) {
            $this->registerFunctions($container);
            $this->registerFilters($container);
            $this->registerTwigExtensions($container);
            // register 'oro_config_value' function
            $securityPolicyDef = $container->getDefinition(self::IBNAB_PMANAGER_SANDBOX_SECURITY_POLICY_SERVICE_KEY);
            $rendererDef = $container->getDefinition(self::IBNAB_TEMPLATE_RENDERER_SERVICE_KEY);

            if ($container->hasDefinition(self::CONFIG_EXTENSION_SERVICE_KEY)) {
                $functions = $securityPolicyDef->getArgument(4);
                $functions = array_merge($functions, ['oro_config_value']);
                $securityPolicyDef->replaceArgument(4, $functions);

                // register an twig extension implements this function
                $rendererDef->addMethodCall('addExtension', [new Reference(self::CONFIG_EXTENSION_SERVICE_KEY)]);
                $rendererDef->addMethodCall('addExtension', [new Reference(self::EMAIL_EXTENSION_SERVICE_KEY)]);
                $filters = $securityPolicyDef->getArgument(1);
                $filters = array_merge(
                        $filters, [
                    'oro_format_price',
                        ]
                );

                $securityPolicyDef->replaceArgument(1, $filters);
                $rendererDef->addMethodCall('addExtension', [new Reference(self::CURRENCY_SERVICE_KEY)]);
                $functions = $securityPolicyDef->getArgument(4);
                $functions = array_merge(
                        $functions, array('url', 'path')
                );
                $securityPolicyDef->replaceArgument(4, $functions);
                $rendererDef->addMethodCall('addExtension', array(new Reference(self::ROUTING_EXTENSION_SERVICE_KEY)));

                //orocommerce
                $functions = array_merge($securityPolicyDef->getArgument(4), ['order_line_items']);
                $tags = array_merge($securityPolicyDef->getArgument(0), ['set']);
                $filters = $securityPolicyDef->getArgument(1);
                $filters = array_merge($filters, ['join']);
                $securityPolicyDef->replaceArgument(0, $tags);
                $securityPolicyDef->replaceArgument(1, $filters);
                $securityPolicyDef->replaceArgument(4, $functions);
                $rendererDef->addMethodCall('addExtension', [new Reference('oro_checkout.twig.line_items')]);

                $filters = $securityPolicyDef->getArgument(1);
                $filters = array_merge(
                        $filters, [
                    'oro_format_short_product_unit_value',
                    'oro_format_product_unit_label',
                        ]
                );
                $securityPolicyDef->replaceArgument(1, $filters);
                $rendererDef->addMethodCall('addExtension', [new Reference('oro_product.twig.product_unit_value')]);
                $rendererDef->addMethodCall('addExtension', [new Reference('oro_product.twig.product_unit_label')]);

                $functions = array_merge($securityPolicyDef->getArgument(4), ['rfp_products']);
                $securityPolicyDef->replaceArgument(4, $functions);
                $rendererDef->addMethodCall('addExtension', [new Reference('oro_rfp.twig.request_products')]);
            }

            if ($container->hasDefinition(self::FORMATTER_EXTENSION_SERVICE_KEY)) {
                $filters = $securityPolicyDef->getArgument(1);
                $filters = array_merge($filters, ['oro_format']);
                $securityPolicyDef->replaceArgument(1, $filters);
                // register an twig extension implements this function
                $rendererDef->addMethodCall('addExtension', [new Reference(self::FORMATTER_EXTENSION_SERVICE_KEY)]);
            }
        }
    }

}
