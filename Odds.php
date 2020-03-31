<?php

class Odds{
    
    private $example_url = "https://www.sportsinteraction.com/specials/us-elections-betting/";
    
    public function load(){
        $odds = new GetOdds($this->example_url);
        return $odds->getData();

        
        //var_dump($odds);
    }
}

class GetOdds{
    private $url;
    public $content;
    public $json_data;    

    function __construct($url){
        $this->url = $url;
        
    }

    function getData(){
        $this->retrieveHTML();
        //print_r($this->content);
        $this->buildJson();

        return $this->json_data;
    }

    function retrieveHTML (){
        $this->content = file_get_contents($this->url);
    }

    /**
     * Find the HTML Elements in the content to fill the JSON
     *
     */
    function buildJson(){
        $json = [];
        //Search data inside content
        $dom = new DOMDocument();
        $dom->loadHTML($this->content);
        $finder = new DOMXPath($dom);

        
        $query = "//div[contains(@id, 'page-container')]";
        $nodes = $finder->query($query);
        //print($nodes->length.' ');
        
        $query = $nodes->item(0)->getNodePath()."//div[contains(@id, 'page')]";
        $nodes = $finder->query($query);
        //print($nodes->length.' ');
                
        $query = $nodes->item(0)->getNodePath()."//div[contains(@data-component, 'event_types/Show')]";
        $nodes = $finder->query($query);
        //print($nodes->length.' ');
        //var_dump($query."<br>");
        
        $temp_json = $this->getArray($nodes->item(0));        
        $temp_json = json_decode($temp_json['data-props']);
        

        foreach ($temp_json->games as $game){
            $data['BetName'] = $game->gameName;
            $data['BetOptions'] = [];

            //var_dump($game);
            $runners = $game->betTypeGroups[0]->betTypes[0]->events[0]->runners;
            foreach ($runners as $runner){
                $runnerData['Outcome'] = $runner->runner;
                $runnerData['Odds'] = $runner->currentPrice;
                $data['BetOptions'][] = $runnerData;
            }
            $json[] = $data;
        }

        //var_dump($json);
        
        $this->json_data = json_encode ($json);
    }

    /**
     * To be able to extract the information inside a DOMNode
     */
    function getArray(DOMNode $node)
    {
        $array = false;

        if ($node->hasAttributes())
        {
            foreach ($node->attributes as $attr)
            {
                $array[$attr->nodeName] = $attr->nodeValue;
            }
        }

        if ($node->hasChildNodes())
        {
            if ($node->childNodes->length == 1)
            {
                $array[$node->firstChild->nodeName] = $node->firstChild->nodeValue;
            }
            else
            {
                foreach ($node->childNodes as $childNode)
                {
                    if ($childNode->nodeType != XML_TEXT_NODE)
                    {
                        $array[$childNode->nodeName][] = $this->getArray($childNode);
                    }
                }
            }
        }

        return $array;
    }
}