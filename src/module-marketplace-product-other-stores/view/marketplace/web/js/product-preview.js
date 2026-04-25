require(['jquery','Lof_Formbuilder/js/jquery.minicolors.min'], function($){
    $(document).ready(function() {
        console.log('ready');
        function prepreviewProduct() {
            const url = 'https://preprod.tcgcollective.co/'; // Replace with the actual URL you want to open
            window.open(url, '_blank');
        }
    });
});