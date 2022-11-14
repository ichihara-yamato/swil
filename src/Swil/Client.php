<?php
namespace SwimCrawl\Client;

/*
 * This file is part of Swim PHP.
 *
 * (c) Yamato Ichihara <https://twitter.com/xxx_ichihara>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use DOMWrap\Document as Document;
use HeadlessChromium\BrowserFactory;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Get user agent class
 */
class Crawl
{
    private function getUA(){
        $list = [
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36 Edg/107.0.1418.35',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36 Edg/107.0.1418.35',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36'
        ];
        

        return $list[array_rand($list)];
    }

    private function checkURL($url = NULL){
        $pattern = '/https?:\/{2}[\w\/:%#\$&\?\(\)~\.=\+\-]+/';
        return preg_match($pattern, $url);
    }

    /**
     * getPageOnChrome function
     *
     * @param [type] $url
     * @param array $options
     * @return void
     */
    public function getPageOnChrome($url = NULL, $options = []){
        if($this->checkURL($url) === 0) return;

        if(count($options) == 0){
            $options = [
                'headless' => true,
                'noSandbox' => false,
                'sendSyncDefaultTimeout' => 5000,
                'windowSize'      => [1200, 3000],
                'userAgent' => $this->getUA(),
                'enableImages' => false,
                // 'keepAlive' => true,
            //	    'connectionDelay' => 3,
    //            'startupTimeout' => 3,
            //	    'customFlags' => [sprintf('-remote-debugging-port=%s', $site[6])]
            ];
        }

        $browserFactory = new BrowserFactory(
        //	'C:\Program Files\Google\Chrome\Application\chrome.exe'
            sprintf('%s\chrome-win\chrome.exe', __DIR__)
        );
        $browser = $browserFactory->createBrowser($options);

        $html = '';
        try {
            // 新しいページを作成し、指定のURLへ移動する
            $page = $browser->createPage();
            
            $navigation = $page->navigate($url);
            $navigation->waitForNavigation();
            
            $evaluation = $page->evaluate('document.documentElement.innerHTML');
            $html = $evaluation->getReturnValue(5000);
        } finally {
            $browser->close();

            if($html != '') return $html;
            else return -1;
        }
    }

    /**
     * getPageOnGoutte function
     *
     * @param [type] $url
     * @param array $options
     * @return void
     */
    public function getPageOnGoutte($url = NULL, $options = []){
        if($this->checkURL($url) === 0) return;

        if(count($options) == 0){
            $options = [
                'timeout' => 60
            ];
        }

        $client = new Client(HttpClient::create($options));
        // $client->setHeader('User-Agent', $this->getUA());
        $crawler = $client->request('GET', $url);
        return $crawler->html();
    }
}