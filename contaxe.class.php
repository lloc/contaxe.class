<?php

/*
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
    
    ContaxePublisherClasses 0.1 Copyright (C) 2007 Dennis Ploetner <dennis@ploetner.it>
*/

define ('CONTAXE_CHANNEL_ID', 'ID DES CHANNELS');

class ContaxeRequest {

    private $url;
    private $param;

    function __construct ($len = 1) {
        $this->url = 'http://www.contaxe.com/go/xml?';
        $this->param = array (
            'c' => CONTAXE_CHANNEL_ID,
            'ref' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
            'len' => is_numeric ($len) ? $len : 1,
            'remoteip' => $_SERVER['REMOTE_ADDR'],
        );
    }
    
    function getFormats () {
        if (isset ($this->param['format'])) {
            return array ($this->param['format']);
        }
        return array ('img', 'imgtxt', 'flash');
    }

    function getFormat () {
        return (isset ($this->param['format']) ? $this->param['format']:'txt');
    }

    function setFormat ($str) {
        if (in_array ($str, $this->getFormats ())) {
            $this->param['format'] = $str;
        }
    }

    function getDimensions () {
        if (isset ($this->param['format'])) {
            switch ($this->param['format']) {
                case 'img':
                    return range (1, 14);
                    break;
                case 'flash':
                    return range (21, 30);
                    break;
            }
        }
        return array ();
    }

    function getDimension () {
        $dim = array (
            1 => array (468, 60),
            2 => array (728, 90),
            3 => array (234, 60),
            4 => array (120, 240),
            5 => array (180, 150),
            6 => array (300, 250),
            7 => array (336, 280),
            8 => array (240, 400),
            9 => array (120, 600),
            10 => array (160, 600),
            11 => array (120, 90),
            12 => array (120, 60),
            13 => array (88, 31),
            14 => array (80, 15),
            21 => array (468, 60),
            22 => array (728, 90),
            23 => array (234, 60),
            24 => array (120, 240),
            25 => array (180, 150),
            26 => array (300, 250),
            27 => array (336, 280),
            28 => array (240, 400),
            29 => array (120, 600),
            30 => array (160, 600),
        );
        return (
            isset ($this->param['t']) && isset ($dim[$this->param['t']]) ?
            $dim[$this->param['t']] :
            array ()
        );
    }
    
    function setDimension ($n) {
        if (in_array ($n, $this->getDimensions ())) {
            $this->param['t'] = $n;
        }
    }

    function setCountry ($str) {
        if (in_array ($str, array ('de', 'at', 'ch'))) {
            $this->param['cty'] = $str;
        }
    }

    function setFillRandom () {
        $this->param['rnd'] = 1;
        $this->param['ofs'] = 0;
    }

    function setOffset ($n) {
        $this->param['ofs'] = is_numeric ($n) ? $n : 0;
        $this->param['rnd'] = 0;
    }

    function setTrackingSubID ($str) {
        $this->param['tsi'] = substr ($str, 0, 50);
    }

    function setNoCrawl () {
        $this->param['nocrawl'] = 1;
    }

    function setQuery ($str) {
        $this->param['query'] = $str;
    }

    function getUrl () {
        $param = array ();
        foreach ($this->param as $key => $value) {
            if (!empty ($value)) {
                $param[] = $key . '=' . urlencode ($value);
            }
        }
        return $this->url . implode ('&', $param);
    }
}

class ContaxeResponse {

    private $xml;
    private $results = array ();

    function __construct ($req) {
        $this->xml = new SimpleXMLElement (file_get_contents ($req->getUrl ()));
        if (isset ($this->xml->results)) {
            foreach ($this->xml->results->result as $obj) {
                $classname = $req->getFormat () . 'Contaxe';
                $this->results[] = new $classname ($obj, $req->getDimension ());
            }
        }
    }

    function generate ($sprtr = '') {
        $retval = array ();
        foreach ($this->results as $obj) {
            $retval[] = $obj->generate ();
        }
        return (!empty ($retval) ? implode ($sprtr, $retval) : '');
    }

}

class ContaxeAdvert {

    private $obj;
    public $width;
    public $height;

    function __construct ($obj, $dim) {
        $args = get_object_vars ($obj);
        foreach ($args as $key => $value) {
            $this->$key = $value;
        }
        $this->width = isset ($dim[0]) ? $dim[0] : 0;
        $this->height = isset ($dim[1]) ? $dim[1] : 0;
    }

    function format ($value) {
        return htmlentities (utf8_decode ($value), ENT_QUOTES);
    }

}

class txtContaxe extends ContaxeAdvert {
    
    function generate () {
        return
            '<a href="' . $this->trackingurl . '" ' .
            'title="' . $this->format ($this->text) . '"' .
            '>' . $this->format ($this->title) . '</a>';
    }

}

class imgContaxe extends ContaxeAdvert {
    
    function generate () {
        return
            '<a href="' . $this->trackingurl . '">' .
            '<img src="' . $this->img . '" ' .
            'width="' . $this->width . '" height="' . $this->height .'" ' .
            'alt="' . $this->format ($this->title) . '" border="0" /></a>';
    }

}

class imgtxtContaxe extends ContaxeAdvert {

    function generate () {
        return
            '<a href="' . $this->trackingurl . '" ' .
            'title="' . $this->format ($this->text) . '">' .
            '<img src="' . $this->img . '" ' .
            'alt="' . $this->displayurl . '" ' .
            'border="0" />' . $this->format ($this->title) . '</a>';
    }

}

class flashContaxe extends ContaxeAdvert {

    function generate () {
        return
            '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" ' .
            'codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0" '.
            'width="' . $this->width . '" height="' . $this->height .'">' .
            '<param name="movie" value="' . $this->movie . '">' .
            '<embed src="' . $this->movie . '" name="' . $this->title . '" '.
            'width="' . $this->width . '" height="' . $this->height .'" ' .
            'type="application/x-shockwave-flash" ' .
            'pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>' .
            '</object>';
    }

}

?>
