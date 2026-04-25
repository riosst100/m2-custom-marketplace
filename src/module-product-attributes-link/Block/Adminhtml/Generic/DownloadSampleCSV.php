<?php

namespace TCGCollective\ProductAttributesLink\Block\Adminhtml\Generic;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;

class DownloadSampleCSV implements ButtonProviderInterface 
{
    protected $context;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    protected function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }

    public function getButtonData() 
    {
        return [];
        
        $url = $this->getUrl(
            '*/*/downloadSampleCsv',
            []
        );

        // return [
        //     'label' => __('Download Sample CSV'),
        //     'class' => 'save primary',
        //     'data_attribute' => [
        //         'mage-init' => ['button' => ['event' => 'save']],
        //         'form-role' => 'save',
        //     ],
        //     'sort_order' => 90,
        // ];
        return [
            'label' => __('Download Sample CSV'),
            // 'on_click' => sprintf("location.href = '%s';", $url),
            'on_click' => "
        require(['uiRegistry'], function (registry) {
            var form = registry.get('download_sample_csv_form.download_sample_csv_form');
            var dataSource = registry.get('download_sample_csv_form.download_sample_csv_form_data_source');

            if (!form || !dataSource) {
                console.error('Form or dataSource not found');
                return;
            }

            // Jalankan validasi form
            form.validate();

            // Cek apakah form valid
            var isValid = true;
            try {
                isValid = !form.source.get('params.invalid');
            } catch (e) {
                console.warn('Cannot read validation result', e);
            }

            if (!isValid) {
                // alert('Please fill all required fields before downloading.');
                return false; // Stop redirect kalau invalid
            }

            // ✅ Ambil data langsung dari form source
            var data = form.source.get('data') || {};

            // ✅ Bangun query string dinamis dari data form
            var params = Object.keys(data)
                .map(function(key) {
                    return encodeURIComponent(key) + '=' + encodeURIComponent(data[key]);
                })
                .join('&');

            // ✅ Redirect tanpa loader
            window.location.href = '{$url}' + (params ? '?' + params : '');
        });
    ",
            'class' => 'primary',
            'sort_order' => 10
        ];
    }
}