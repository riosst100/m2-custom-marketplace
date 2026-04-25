

/**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function modifyMeta(array $meta)
    {        
        $meta = $this->addCustomModal($meta);
        $meta = $this->addCustomModalLink($meta);

        return $meta;
    }

    protected function addCustomModal($meta)
    {
        $meta = array_merge_recursive(
            $meta,
            [
                static::CUSTOM_MODAL_INDEX => $this->getModalConfig(),
            ]
        );

        return $meta;
    }

    protected function getModalConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Modal::NAME,
                        'dataScope' => '',
                        'provider' => static::FORM_NAME . '.product_form_data_source',
                        'ns' => static::FORM_NAME,
                        'options' => [
                            'title' => __('Select Sets'),
                            'buttons' => [
                                [
                                    'text' => __('Add New Sets'),
                                    'class' => 'action-primary', // additional class
                                    'actions' => [
                                        [
                                            'targetName' => 'index = product_form', // Element selector
                                            'actionName' => 'save', // Save parent form (product)
                                        ],
                                        'closeModal', // method name
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'children' => [
                static::CUSTOM_MODAL_CONTENT => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender' => false,
                                'componentType' => 'container',
                                'dataScope' => 'data.product', // save data in the product data
                                'externalProvider' => 'data.product_data_source',
                                'ns' => static::FORM_NAME,
                                'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                'realTimeLink' => true,
                                'behaviourType' => 'edit',
                                'externalFilterMode' => true,
                                'currentProductId' => $this->locator->getProduct()->getId(),
                            ],
                        ],
                    ],
                    'children' => [
                        static::CUSTOM_MODAL_FIELDSET => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'label' => __('Fieldset'),
                                        'componentType' => Fieldset::NAME,
                                        'dataScope' => 'custom_data',
                                        'collapsible' => true,
                                        'sortOrder' => 10,
                                        'opened' => true,
                                    ],
                                ],
                            ],
                            'children' => [
                                static::CONTAINER_HEADER_NAME => $this->getHeaderContainerConfig(10),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getHeaderContainerConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => null,
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'template' => 'ui/form/components/complex',
                        'sortOrder' => $sortOrder,
                        'content' => __('You can write any text here'),
                    ],
                ],
            ],
            'children' => [],
        ];
    }
    protected function addCustomModalLink($meta)
    {
        $sortOrder = 10;

        $meta = array_merge_recursive(
            $meta,
            [
                static::GROUP_SETS => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Sets'),
                                'collapsible' => true,
                                'opened' => true,
                                'componentType' => Form\Fieldset::NAME,
                                'sortOrder' => $this->getNextGroupSortOrder(
                                    $meta,
                                    self::$groupContent,
                                    self::$sortOrder
                                ),
                            ],
                        ],
                    ],
                    'children' => $this->getPanelChildren()
                ]
            ]
        );

        return $meta;
    }

    /**
     * Prepares panel children configuration
     *
     * @return array
     */
    protected function getPanelChildren()
    {
        return [
            'configurable_products_button_set' => $this->getButtonSet(),
            // 'configurable-matrix' => $this->getGrid(),
        ];
    }

    /**
     * Returns Buttons Set configuration
     *
     * @return array
     */
    protected function getButtonSet()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'Magento_ConfigurableProduct/js/components/container-configurable-handler',
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'content1' => __(
                            'Select existing sets or create new ones.'
                        ),
                        'template' => 'ui/form/components/complex',
                        'createConfigurableButton' => 'ns = ${ $.ns }, index = create_configurable_products_button',
                        '__disableTmpl' => ['createConfigurableButton' => false],
                    ],
                ],
            ],
            'children' => [
                'select_sets_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => Container::NAME,
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/form/components/button',
                                'actions' => [
                                    [
                                        'targetName' => 'ns=' . static::FORM_NAME . ', index='. static::CUSTOM_MODAL_INDEX,
                                        'actionName' => 'openModal',
                                    ],
                                ],
                                'title' => __('Select Sets'),
                                'sortOrder' => 20
                                // 'title' => __('Open Custom Modal'),
        //                                         'formElement' => Container::NAME,
        //                                         'componentType' => Container::NAME,
        //                                         'component' => 'Magento_Ui/js/form/components/button',
        //                                         'actions' => [
        //                                             [
        //                                                 'targetName' => 'ns=' . static::FORM_NAME . ', index='
        //                                                     . static::CUSTOM_MODAL_INDEX, // selector
        //                                                 'actionName' => 'openModal', // method name
        //                                             ],
        //                                         ],
        //                                         'displayAsLink' => false,
        //                                         'sortOrder' => $sortOrder,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }