<?php
/** createchart-archive.js.php
 * WTherm web-connected thermostat https://github.com/NiekProductions/WTherm/
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * This file outputs javascript code to work with Google Charts (https://developers.google.com/chart/?hl=nl)
 * it uses a modified class from 'PHP class for google chart tools' (https://code.google.com/p/php-class-for-google-chart-tools/)
 */
 
header('Content-Type: application/javascript');
header("Cache-Control: no-cache, must-revalidate"); // Tell browser not to cache this script
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include('/usr/local/bin/WTherm/db.php'); // Connect to the database

// Fetch the archived values for the last week (except for the last day)
$sql = "SELECT * FROM archive WHERE `time` > DATE_SUB(NOW(), INTERVAL 7 DAY) AND `time` < DATE_SUB(NOW(), INTERVAL 1 DAY) ORDER BY time ASC";
$stmt = $db->prepare($sql);
$stmt->execute();
$records = $stmt->fetchAll();

// Fetch the logged values for the last day
$sql = "SELECT `time`, MIN(`temp`) as min_temp, MAX(`temp`) as max_temp, MIN(`outside_temp`) as outside_min_temp, MAX(`outside_temp`) as outside_max_temp, MIN(`humidity`) as min_humidity, MAX(`humidity`) as max_humidity FROM log WHERE `time` > DATE_SUB(NOW(), INTERVAL 1 DAY) GROUP BY DATE(`time`), HOUR(`time`) ORDER BY `time` ASC";
$stmt = $db->prepare($sql);
$stmt->execute();
$records2 = $stmt->fetchAll();

$records = array_merge($records, $records2);

$chart = new Chart('LineChart');

$data = array(
        'cols' => array(
                array('id' => '', 'label' => 'Date', 'type' => 'datetime', 'role' => 'domain'), //Date
                array('id' => '', 'label' => 'Room', 'type' => 'number', 'role' => 'data'), //Room temp
        ),
        'rows' => array(
        )
);

foreach($records as $record){
	$newrow = array('c' => array(
		array('v' => '%%new Date('.(strtotime($record['time'])*1000).') %%'), //Date 
		array('v' => round(($record['min_temp']+$record['max_temp'])/2,1)), //Room temp
	));
	array_push($data['rows'], $newrow);
}

$json = json_encode($data);
$json = preg_replace("/(('|\")%%|%%(\"|'))/",'', $json);
$chart->load($json);

$options = array(
	'title' => 'Temperature', 
	'theme' => 'null', 
	'legend' => array(
		'position' => 'in',
	),
	'curveType' => 'none', 
	'series' => array(
		0 => array(
			'color' => '#3366CC', // Color for the room temperature (cooling) line
		),
		1 => array(
			'color' => '#DC3912', // Color for the room temperature (heating) line
		),
	),
	'chartArea' => array(
		'left' => 45,
		'width' => '100%',
	),
	'vAxis' => array(
		'title' => 'Temperature (°C)',
		'titleTextStyle' => array(
			'italic' => false,
		),
/*		'viewWindow' => array(
			'min' => round(($range['mintemp'] <= $range['minttemp'] ? $range['mintemp'] : $range['minttemp']) - 1.0, 0, PHP_ROUND_HALF_UP),
			'max' => round(($range['maxtemp'] >= $range['maxttemp'] ? $range['maxtemp'] : $range['maxttemp']) + 1.5, 0, PHP_ROUND_HALF_DOWN),
		),*/
	),
	'hAxis' => array(
		'title' => 'Time',
		'gridlines' => array(
			'count' => -1,
		),
		'titleTextStyle' => array(
			'italic' => false,
		),
		
	),
	'width' => '100%',
	'height' => 250,
);

echo $chart->draw('temps', $options);


$chart = new Chart('LineChart');

$data = array(
        'cols' => array(
                array('id' => '', 'label' => 'Date', 'type' => 'datetime', 'role' => 'domain'), //Date
                array('id' => '', 'label' => 'Room', 'type' => 'number', 'role' => 'data'), //Room humidity
        ),
        'rows' => array(
        )
);

foreach($records as $record){
	$newrow = array('c' => array(
		array('v' => '%%new Date('.(strtotime($record['time'])*1000).') %%'), //Date 
		array('v' => round(($record['min_humidity'] + $record['max_humidity'])/2,1)), //Humidity
	));
	array_push($data['rows'], $newrow);
}

$json = json_encode($data);
$json = preg_replace("/(('|\")%%|%%(\"|'))/",'', $json);
$chart->load($json);

$options = array(
	'title' => 'Humidity', 
	'theme' => 'null', 
	'legend' => array(
		'position' => 'in',
	),
	'curveType' => 'none', 
	'series' => array(
		0 => array(
			'color' => '#3366CC',
		),
	),
	'chartArea' => array(
		'left' => 45,
		'width' => '100%',
	),
	'vAxis' => array(
		'title' => 'Relative humidity (%)',
/*		'minValue' => 0,
		'maxValue' => 100,*/
		'titleTextStyle' => array(
			'italic' => false,
		),
	),
	'hAxis' => array(
		'title' => 'Time',
		'titleT1extStyle' => array(
			'italic' => false,
		),
		'gridlines' => array(
			'count' => -1,
		),
		
	),
	'width' => '100%', 
	'height' => 250,
);

echo $chart->draw('humid', $options);
?>	
window.onresize=function(){
	drawCharts();
};

window.onload=function(){
	drawCharts();
};

function drawCharts(){
	drawChart1();
	drawChart2();
}

<?php
/*
 * Slightly modified 'PHP class for google chart tools'
 * From https://code.google.com/p/php-class-for-google-chart-tools/
 */

class Chart {
        
        private static $_first = true;
        private static $_count = 0;
        
        private $_chartType;
        
        private $_data;
        private $_dataType;
        private $_skipFirstRow;
        
        /**
         * sets the chart type and updates the chart counter
         */
        public function __construct($chartType, $skipFirstRow = false){
                $this->_chartType = $chartType;
                $this->_skipFirstRow = $skipFirstRow;
                self::$_count++;
        }
        
        /**
         * loads the dataset and converts it to the correct format
         */
        public function load($data, $dataType = 'json'){
                $this->_data = ($dataType != 'json') ? $this->dataToJson($data) : $data;
        }
        
        /**
         * load jsapi
         */
        private function initChart(){
                self::$_first = false;
                
                $output = '';
                // start a code block
                //$output .= '<script type="text/javascript" src="https://www.google.com/jsapi"></script>'."\n";
                //$output .= '<script type="text/javascript">google.load(\'visualization\', \'1.0\', {\'packages\':[\'corechart\']});</script>'."\n";
                
                return $output;
        }
        
        /**
         * draws the chart
         */
        
        public function draw($div, Array $options = array()){
                $output = '';
                
                if(self::$_first)$output .= $this->initChart();
                
                // start a code block
                //$output .= '<script type="text/javascript">';

                // set callback function
                //$output .= 'google.setOnLoadCallback(drawChart' . self::$_count . ');';
                
                // create callback function
                $output .= 'function drawChart' . self::$_count . '(){';
                
                $output .= 'var data = new google.visualization.DataTable(' . $this->_data . ');';
                
                // set the options
                $output .= 'var options = ' . json_encode($options) . ';';
                
                // create and draw the chart
                $output .= 'var chart = new google.visualization.' . $this->_chartType . '(document.getElementById(\'' . $div . '\'));';
                $output .= 'chart.draw(data, options);';
                
                $output .= '} ' . "\n";//$output .= '} </script>' . "\n";
                return $output;
        }
                
        /**
         * substracts the column names from the first and second row in the dataset
         */
        private function getColumns($data){
                $cols = array();
                foreach($data[0] as $key => $value){
                        if(is_numeric($key)){
                                if(is_string($data[1][$key])){
                                        $cols[] = array('id' => '', 'label' => $value, 'type' => 'string');
                                } else {
                                        $cols[] = array('id' => '', 'label' => $value, 'type' => 'number');
                                }
                                $this->_skipFirstRow = true;
                        } else {
                                if(is_string($value)){
                                        $cols[] = array('id' => '', 'label' => $key, 'type' => 'string');
                                } else {
                                        $cols[] = array('id' => '', 'label' => $key, 'type' => 'number');
                                }
                        }
                }
                return $cols;
        }
        
        /**
         * convert array data to json
         * info: http://code.google.com/intl/nl-NL/apis/chart/interactive/docs/datatables_dataviews.html#javascriptliteral
         */
        private function dataToJson($data){
                $cols = $this->getColumns($data);
                
                $rows = array();
                foreach($data as $key => $row){
                        if($key != 0 || !$this->_skipFirstRow){
                                $c = array();
                                foreach($row as $v){
                                        $c[] = array('v' => $v);
                                }
                                $rows[] = array('c' => $c);
                        }
                }
                
                return json_encode(array('cols' => $cols, 'rows' => $rows));
        }
        
}
?>


