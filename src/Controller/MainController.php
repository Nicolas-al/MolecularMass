<?php

namespace App\Controller;

use App\Service\FormulaService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{

    public function __construct(RequestStack $requestStack, FormulaService $formulaService)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->formulaService = $formulaService;
    }
    /**
     * @Route("/", name="main")
     */
    public function index(FormulaService $formulaService): Response
    {
        // $formulas = $formulaService->calculateAll();
        // $formulaService->space();

        return $this->render('main.html.twig',[
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/MainController.php',
        ]);
    }

    /**
     * @Route("/formula/calculate", name="ajax_formula")
     */
    public function spaceFormula()
    {
        $formula = $this->request->query->get('formula');
        
        // dd($this->formulaService->calculateOne($formula));
        if("error" === $this->formulaService->calculateOne($formula)){
            $result = $this->formulaService->calculateOne($formula);
        }else{
            
            if(strpos($formula, "(") !== false){
                $newFormula = $this->formulaService->cleanFormula($formula);
                $newFormula = $this->formulaService->spaceOne($newFormula);
                if($this->formulaService->deleteDoublon($newFormula) !== false){
                    $newFormula = $this->formulaService->deleteDoublon($newFormula);
                }
                $result = [$this->formulaService->getAtoms($newFormula), json_encode($this->formulaService->calculateOne($formula)), $this->formulaService->getMassAtom($newFormula)];
            }else{
                $newFormula = $this->formulaService->spaceOne($formula);
                if($this->formulaService->deleteDoublon($newFormula) !== false){
                    $newFormula = $this->formulaService->deleteDoublon($newFormula);
                }
                $result = [$this->formulaService->getAtoms($newFormula), json_encode($this->formulaService->calculateOne($formula)), $this->formulaService->getMassAtom($newFormula)];
            }
        };
        
        return new Response(json_encode($result));
    }

    /**
     * @Route("/legalNotice", name="legal_notice")
     */
    public function legalNotice()
    {
        return $this->render('legal-notice.html.twig',[
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/MainController.php',
        ]);
    }
}
