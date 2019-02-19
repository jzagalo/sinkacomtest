<?php 

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MapController extends Controller {

	/**
	 * @Route("/")
	 * @Method("GET")
	 */

	public function index() {
		$cordinates = array();

		$csv = explode("\n", file_get_contents('./../fixtures/Testdaten-Node-csv.csv'));
		$csv = array_splice($csv, 1);
		$minX = 9999999; $minY = 9999999;

		foreach ($csv as $key => $value) {
		  $data = explode(";", $value);	
		  $cordinates[$data[0]] = (array)array_splice($data, 1, 3);		 
		}			
		
		$cleanedCoordinates = array_splice($cordinates, 0, sizeof($cordinates)-1);

		$scaledCordinates = array();
		foreach ($cleanedCoordinates as $key=>$value) {
			$scaledCordinates[$key][0] = $value[0];
			$scaledCordinates[$key][1] = ((float)$value[1] -3559000);
			$scaledCordinates[$key][2] = ((float)$value[2] - 5330300);	
		}

		return $this->render('maps/index.html.twig', array(
		 'plans' =>	$scaledCordinates
		));
	}
}





