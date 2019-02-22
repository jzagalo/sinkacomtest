<?php 

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class MapController extends Controller {

	/**
	 * @Route("/" , name="submit_route")
	 * @Method({"GET", "POST"})
	 */

	public function index(Request $request) {

		$cordinates = $scaledCordinates = $locations = array();		

		$csv = explode("\n", file_get_contents('./../fixtures/Testdaten-Node-csv.csv'));
		$csv = array_splice($csv, 1);

		
		

		foreach ($csv as $key => $value) {
		  $data = explode(";", $value);	

		  $cordinates[$data[0]] = array_splice($data, 1, 3);		 
		}			
		
		$cleanedCoordinates = array_splice($cordinates, 0, sizeof($cordinates)-1);				
        list($minX, $minY) = $this->getMinimunCordinates($cleanedCoordinates);
      

		foreach ($cleanedCoordinates as $key=>$value) {
			$scaledCordinates[$key][0] = $value[0];	
			$xVal = (float)$value[1];
			$yVal = (float)$value[2];			

			$scaledCordinates[$key][1] = ($xVal - $minX);
			$scaledCordinates[$key][2] = ($yVal - $minY);

			$locations[$value[0]] = $key + 1;	
		}

		$form = $this->createLocationsForm($locations);	

		if($request->getMethod() === 'GET') {	
			return $this->render('maps/index.html.twig', array(
			 'plans' =>	$scaledCordinates,
			 'form' => $form->createView(),
			 'minY' => $minY, 
			 'minX' => $minX

			));

		} else {

		   $formData = $request->getContent();
		   $arrayFormData = json_decode($formData, true);
		   $routes = array_values($arrayFormData);
		   $routes = array_map("intval", $routes);
		   $minRoute = min($routes); 
		   $maxRoute = max($routes); 		   
		   $range = range($minRoute, $maxRoute);


           $testdatenPipes = $this->fetchNodePipes();
          
		   $filteredPipes = array_filter($testdatenPipes, function($pipes) use($minRoute, $maxRoute) {
		   	
		   	   $values = array_values($pipes);

		   	   $quelle =(float) $values[0];
		   	   $ziel = (float) $values[1];		   

		   	  // return in_array($ziel, $range, true) || in_array($quelle, $range, true);
		   	   return ($quelle === $minRoute && $ziel === $maxRoute);
		   });

		   return $this->json($filteredPipes);
			/*$form->handleRequest($request);

	        if ($form->isSubmitted() && $form->isValid()) {
	            $data = $form->getData();

	            
	        }*/

		}
	}

	private function getMinimunCordinates($cordinates) {
		$minX = 9999999; $minY = 9999999;

		foreach ($cordinates as $key=>$value) {		
			$xVal = (float)$value[1];
			$yVal = (float)$value[2];

			if($yVal < $minY){
				$minY = $yVal;
			}

			if($xVal < $minX){
				$minX = $xVal;
			}
		}

		return array($minX, $minY);
	}

	public function createLocationsForm($locations) {
		$form = $this->createFormBuilder()
            ->add('From', ChoiceType::class, [
			    'choices'  => $locations,
			])
			->add('To', ChoiceType::class, [
			    'choices'  => $locations,
			])
            ->getForm();

        return $form;
	}

	private function fetchNodePipes(){
		$csv = explode("\n", file_get_contents('./../fixtures/Testdaten-Pipe-csv.csv'));
		$csv = array_splice($csv, 1);
		$data = $cordinates = array();

		foreach ($csv as $key => $value) {
		  $data = explode(";", $value);	

		  $cordinates[$data[0]] = array_splice($data, 1, 2);		 
		}

		$cordinates = array_splice($cordinates, 0, sizeof($cordinates)-1);
		return $cordinates;
	}

	private function filterRoutes(){

	}
}





