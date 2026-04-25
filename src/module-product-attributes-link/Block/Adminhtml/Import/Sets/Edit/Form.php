<?php

namespace TCGCollective\ProductAttributesLink\Block\Adminhtml\Import\Sets\Edit;

use Magento\Framework\View\Asset\Repository; 
use TCGCollective\ProductAttributesLink\Helper\Data as MappingHelper;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected $_assetRepo;
    protected $helper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        MappingHelper $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }


    protected function _prepareForm()
    {   
        $path = $this->_assetRepo->getUrl("TCGCollective_ProductAttributesLink::sample/attribute_set_mapping_sample.csv");

        $model = $this->_coreRegistry->registry('row_data');

        $form = $this->_formFactory->create(
            ['data' => [
                    'id' => 'edit_form',
                    'enctype' => 'multipart/form-data',
                    'action' => $this->getData('action'),
                    'method' => 'post'
                ]
            ]);

        $form->setHtmlIdPrefix('datalocation_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => '', 'class' => 'fieldset-wide']
        );

        $entity = $this->getRequest()->getParam('entity') ?: '';

        $fieldset->addField(
            'entity',
            'select',
            array(
                'label' => __("Categories"),
                'class' => 'required-entry',
                'required' => 'true',
                'name' => 'entity',        
                'value' => $entity,
                'values'    => [
                    [
                        'value' => '',
                        'label' => __('Select...')
                    ],
                    ['label' => 'Card Games', 'value' => 'card_game'],
                    ['label' => 'Bricks', 'value' => 'bricks_brand'],
                    ['label' => 'Miniature Games', 'value' => 'miniature_game'],
                    ['label' => 'Living Card Games', 'value' => 'living_card_game'],
                    ['label' => 'Non Sport Card', 'value' => 'non_sport_card'],
                    ['label' => 'Sport Card', 'value' => 'sport_card']
                ]
            )
        );

        $fieldset->addField(
            'categories', 
            'select',
            array(
                'label' => "",
                'class' => 'requifed-entry',
                'required' => 'true',
                'name' => 'categories',        
                'value' => '',
                'values'    => [
                    [
                        'value' => '',
                        'label' => __('Select...')
                    ]
                ]
            )
        );

        // MINIATURE GAME
        // $miniatureGameItems = [];
        // $miniatureGameItems[] = [
        //     'value' => '',
        //     'label' => __('--Please select--')
        // ];
        // $miniatureGameOptions = $this->helper->getAttributeOptions('miniature_game');
        // if ($miniatureGameOptions) {
        //     foreach($miniatureGameOptions as $option) {
        //         if ($option->getLabel() && $option->getValue()) {
        //             $miniatureGameItems[] = [
        //                 'value' => $option->getValue(),
        //                 'label' => $option->getLabel()
        //             ];
        //         }
        //     }
        // }

        // $miniatureGame = $this->getRequest()->getParam('miniature_game') ?: null;

        // $fieldset->addField(
        //     'miniature_game', 
        //     'select',
        //     array(
        //         'display' => 'none',
        //         'label' => __("Miniature Game"),
        //         'class' => 'required-entry',
        //         'required' => 'true',
        //         'name' => 'miniature_game',
        //         'value' => $miniatureGame,
        //         'values'    => $miniatureGameItems
        //     )
        // );

        // // LIVING CARD GAME
        // $livingCardGamesItems = [];
        // $livingCardGamesItems[] = [
        //     'value' => '',
        //     'label' => __('--Please select--')
        // ];
        // $livingCardGamesOptions = $this->helper->getAttributeOptions('living_card_game');
        // if ($livingCardGamesOptions) {
        //     foreach($livingCardGamesOptions as $option) {
        //         if ($option->getLabel() && $option->getValue()) {
        //             $livingCardGamesItems[] = [
        //                 'value' => $option->getValue(),
        //                 'label' => $option->getLabel()
        //             ];
        //         }
        //     }
        // }

        // $livingCardGames = $this->getRequest()->getParam('living_card_game') ?: null;

        // $fieldset->addField(
        //     'living_card_game', 
        //     'select',
        //     array(
        //         'display' => 'none',
        //         'label' => __("Living Card Game"),
        //         'class' => 'required-entry',
        //         'required' => 'true',
        //         'name' => 'living_card_game',
        //         'value' => $livingCardGames,
        //         'values'    => $livingCardGamesItems
        //     )
        // );

        // // LEGO
        // // $legoItems = [
        // //     [
        // //         'value' => '',
        // //         'label' => __('--Please select--')
        // //     ],
        // //     [
        // //         'value' => 'legosets',
        // //         'label' => __('Lego Sets')
        // //     ],
        // //     [
        // //         'value' => 'legosingles',
        // //         'label' => __('LEGO Minifigures')
        // //     ]
        // // ];

        // // $lego = $this->getRequest()->getParam('lego') ?: null;

        // // $fieldset->addField(
        // //     'lego', 
        // //     'select',
        // //     array(
        // //         'display' => 'none',
        // //         'label' => __("Lego"),
        // //         'class' => 'required-entry',
        // //         'required' => 'true',
        // //         'name' => 'lego',
        // //         'value' => $lego,
        // //         'values'    => $legoItems
        // //     )
        // // );

        // // SPORT CARD
        // $sportCardItems = [];
        // $sportCardItems[] = [
        //     'value' => '',
        //     'label' => __('--Please select--')
        // ];
        // $sportCardOptions = $this->helper->getAttributeOptions('sc_categories');
        // if ($sportCardOptions) {
        //     foreach($sportCardOptions as $option) {
        //         if ($option->getLabel() && $option->getValue()) {
        //             $sportCardItems[] = [
        //                 'value' => $option->getValue(),
        //                 'label' => $option->getLabel()
        //             ];
        //         }
        //     }
        // }

        // $sportCard = $this->getRequest()->getParam('sport_card') ?: null;

        // $fieldset->addField(
        //     'sport_card', 
        //     'select',
        //     array(
        //         'display' => 'none',
        //         'label' => __("Sport Card"),
        //         'class' => 'required-entry',
        //         'required' => 'true',
        //         'name' => 'sport_card',
        //         'value' => $sportCard,
        //         'values'    => $sportCardItems
        //     )
        // );

        // // NON SPORT CARD
        // $nonSportCardItems = [];
        // $nonSportCardItems[] = [
        //     'value' => '',
        //     'label' => __('--Please select--')
        // ];
        // $nonSportCardOptions = $this->helper->getAttributeOptions('nsc_categories');
        // if ($nonSportCardOptions) {
        //     foreach($nonSportCardOptions as $option) {
        //         if ($option->getLabel() && $option->getValue()) {
        //             $nonSportCardItems[] = [
        //                 'value' => $option->getValue(),
        //                 'label' => $option->getLabel()
        //             ];
        //         }
        //     }
        // }

        // $nonSportCard = $this->getRequest()->getParam('non_sport_card') ?: null;

        // $fieldset->addField(
        //     'non_sport_card', 
        //     'select',
        //     array(
        //         'display' => 'none',
        //         'label' => __("Non Sport Card"),
        //         'class' => 'required-entry',
        //         'required' => 'true',
        //         'name' => 'non_sport_card',
        //         'value' => $nonSportCard,
        //         'values'    => $nonSportCardItems
        //     )
        // );

        // // CARD GAME
        // $items = [];
        // $items[] = [
        //     'value' => '',
        //     'label' => __('--Please select--')
        // ];
        // $cardGameOptions = $this->helper->getAttributeOptions('card_game');
        // if ($cardGameOptions) {
        //     foreach($cardGameOptions as $option) {
        //         if ($option->getLabel() && $option->getValue()) {
        //             $items[] = [
        //                 'value' => $option->getValue(),
        //                 'label' => $option->getLabel()
        //             ];
        //         }
        //     }
        // }

        // $cardGame = $this->getRequest()->getParam('card_game') ?: null;

        // $fieldset->addField(
        //     'card_game', 
        //     'select',
        //     array(
        //         'display' => 'none',
        //         'label' => __("Card Game"),
        //         'class' => 'required-entry',
        //         'required' => 'true',
        //         'name' => 'card_game',
        //         'value' => $cardGame,
        //         'values'    => $items
        //     )
        // );

        $fieldset->addField(
            'import_type', 
            'hidden',
            array(
                'label' => '',
                'name' => 'import_type',        
                'value' => 'sets'
            )
        );

       $importdata_script =  $fieldset->addField(
            'importdata',
            'file', 
                array(
                        'label'     => 'Select File',
                        'required'  => true,
                        'name'      => 'importdata',  
                        'note' => 'Allow File type: .csv', 
                     )
        );

        $importdata_script->setAfterElementHtml("   
        <style>
        .field-categories {
        display:none;
    }
        </style>
        

            <script type=\"text/javascript\">

            

            document.getElementById('attributeset_importdata').onchange = function () { 

                var fileInput = document.getElementById('attributeset_importdata');

                var filePath = fileInput.value;

                var allowedExtensions = /(\.csv)$/i; 

                if(!allowedExtensions.exec(filePath))
                {
                    alert('Please upload file having extensions .csv only.');
                    fileInput.value = '';
                }

            };

            </script>"
        );

        $htmlIdPrefix = $form->getHtmlIdPrefix();

        // $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
  
}