<?php

namespace TCGCollective\MarketPlace\Console\Command;

use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PurgeVarnishCache extends Command
{
    /** @var State * */
    private $state;

    const API_URL = "https://api.cloudways.com/api/v1";

    protected $auth_key;
    protected $auth_email;
    protected $accessToken;

    public function __construct(
        State $state
    ) {
        $this->state = $state;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND); // or \Magento\Framework\App\Area::AREA_FRONTEND, depending on your needs

            $key = '3hiOVvxjGeZ1i2jFdZXYTFQdWIindq';
            $email = 'yewpoh@gmail.com';
            $server_id = '1189703';

            $this->CloudwaysAPIClient($key, $email);
            
            $data = [
                'server_id' => $server_id,
                'action' => 'purge'
            ];

            $msg = 'Varnish cache purge failed';

            $result = $this->request('POST', '/service/varnish', $data);
            if (isset($result->status) && $result->status == "true") {
                $msg = 'Varnish cache purge success.';
            }
            
            $output->writeln($msg);

            return 1;
        } catch (\Exception $e) {
            $output->writeln("Error: ".$e->getMessage());
            $output->writeln("Varnish cache purge failed.");
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("tcg:varnish:purge");
        $this->setDescription("Purge Varnish Cache!");
        parent::configure();
    }

    protected function CloudwaysAPIClient($key, $email) {
        $this->auth_key = $key;
        $this->auth_email = $email;
        $this->prepare_access_token();
    }

    protected function prepare_access_token() {
        $data = [
            'email' => $this->auth_email,
            'api_key' => $this->auth_key
        ];

        $response = $this->request('POST', '/oauth/access_token', $data);
        $this->accessToken = $response->access_token;
    }

    protected function request($method, $url, $post = []) 
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_URL, self::API_URL . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        do {
            if ($this->accessToken) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $this->accessToken]);
            }

            //Set Post Parameters
            $encoded = '';
            if (count($post)) {
                foreach ($post as $name => $value) {
                    $encoded .= urlencode($name) . '=' . urlencode($value) . '&';
                }

                $encoded = substr($encoded, 0, strlen($encoded) - 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
                curl_setopt($ch, CURLOPT_POST, 1);
            }

            $output = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);      

            # ACCESS TOKEN HAS EXPIRED, so regenerate and retry

            if ($httpcode == '401') {
                $this->prepare_access_token();    
            }

        } while ($httpcode == '401');

        if ($httpcode != '200') {
            die('An error occurred code: ' . $httpcode . ' output: ' . substr($output, 0, 10000));
        }

        curl_close($ch);
        return json_decode($output);
    }
}
