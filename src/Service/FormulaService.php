<?php

namespace App\Service;

use App\Repository\AtomeRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Parser\Multiple;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class FormulaService
{
    public function __construct(ProductRepository $productRepo, AtomeRepository $atomRepo, EntityManagerInterface $em)
    {
        $this->productRepo = $productRepo;
        $this->atomRepo = $atomRepo;
        $this->em = $em;
    }

    public function getMassAtom($formula){

        $tabAtomNumber = $this->getAtoms($formula);
        $atomsMass = [];
        foreach ($tabAtomNumber as $atom) {
            $atomDb = $this->atomRepo->findOneBy(['name' => $atom[0]]);
            if($atomDb != null){
                $atomsMass[] = round($atomDb->getMass(), 4);
            }
        }
        return $atomsMass;
    }

    public function getPositionsClosedParenthesis($formula){
        $positions = array();
        $pos = -1;
        while (($pos = strpos($formula, ")", $pos+1)) !== false) {
            $positions[] = $pos;
        }
        // $result = implode(', ', $positions);
        
        return $positions;
    }

    public function getPositionsOpenParenthesis($formula){
        $positions = array();
        $pos = -1;
        while (($pos = strpos($formula, "(", $pos+1)) !== false) {
            $positions[] = $pos;
        }
        // $result = implode(', ', $positions);
        
        return $positions;
    }

    // permet de recalculer une formule ou il y a des parenthèses. 
    // on multiplie les nombre d'atomes par le chiffre après la parenthèse et on return la formule sans parenthèse.
    public function cleanFormula($formula){

        // on vérifie si dans la formule il y a des parenthèses
        if (strpos($formula, "(") !== false && strpos($formula, ")") !== false){
            $positionsClosedParenthèsis = $this->getPositionsClosedParenthesis($formula);
            $positionsOpenParenthèsis = $this->getPositionsOpenParenthesis($formula);
            $multiple = [];

            foreach ($positionsClosedParenthèsis as $key => $positionClosedParenthèsis) {
                // On vérifie si après la parenthèse de fermeture c'est un chiffre sinon on renvoi 1 dans le tableau multiple.
                $afterParenthésis = substr($formula, $positionClosedParenthèsis + 1, null);
                $afterParenthésis = str_split($afterParenthésis);
                $multipleChiffre = null;
                
                    if (is_numeric($afterParenthésis[0])) {
                        $multipleChiffre = $afterParenthésis[0];
                        foreach ($afterParenthésis as $key2 => $item) {
                            
                            if ($key2 > 0) {
                                if (is_numeric($afterParenthésis[$key2 - 1])) {
                                    if (is_numeric($afterParenthésis[$key2])) {
                                        $multipleChiffre = $multipleChiffre . $item;
                                    }
                                }else{
                                    break;
                                }
                            }
                        }
                        $multiple[] = $multipleChiffre;
                    }
                    else{
                        $multiple[] = "Vide";
                    }
                    // On vérifie 
                    if($key > 0){

                        if(empty($nbOccurencesRemoves)){
                            if($multiple[$key - 1] === "Vide"){
                                $nbOccurencesRemoves = 0;
                            }else{
                                $nbOccurencesRemoves = strlen($multiple[$key - 1]);
                            }
                        }else{
                            if($multiple[$key - 1] !== "Vide"){
                                $nbOccurencesRemoves = ($nbOccurencesRemoves) + strlen($multiple[$key - 1]);
                            }
                        }
                        $positionsClosedParenthèsis[$key] = ($positionsClosedParenthèsis[$key]) - $nbOccurencesRemoves;
                        $positionsOpenParenthèsis[$key] = ($positionsOpenParenthèsis[$key] - $nbOccurencesRemoves) + 1;
                    }
                }

                foreach($positionsClosedParenthèsis as $key => $positionClosedParenthèsis){
                    // si la taille de la formule est différente de la position de la parenthèse fermé
                    if (strlen($formula) - 1 !== $positionClosedParenthèsis){
                        // on supprime les multiples de la chaine
                        if(strpos($formula[$positionClosedParenthèsis], ")") !== false && ctype_upper($formula[$positionClosedParenthèsis + 1]) === true){
                            $formula = substr_replace($formula, "", $positionClosedParenthèsis + 1, 0);
                        }else if(strpos($formula[$positionClosedParenthèsis], ")") !== false && ctype_digit($formula[$positionClosedParenthèsis + 1]) === true){
                            $formula = substr_replace($formula, "", $positionClosedParenthèsis + 1, strlen($multiple[$key]));
                            $positionClosedParenthèsis = $positionClosedParenthèsis - strlen($multiple[$key]);
                        }else if(strpos($formula[$positionClosedParenthèsis], ")") !== false && strpos($formula[$positionClosedParenthèsis + 1], "(") !== false){
                            $formula = substr_replace($formula, "", $positionClosedParenthèsis + 1, 0);
                        }else{
                            if(ctype_lower($formula[$positionClosedParenthèsis + 1]) === true){
                                $positionClosedParenthèsis = $positionClosedParenthèsis - 2;
                                $formula = substr_replace($formula, "", $positionClosedParenthèsis + 1, 0);
                            }else{
                                $positionClosedParenthèsis = $positionClosedParenthèsis - 1;
                                dump($positionClosedParenthèsis);
                                $formula = substr_replace($formula, "", $positionClosedParenthèsis + 1, strlen($multiple[$key]));
                            }
                            
                        }
                    }

                }
                $positionsClosedParenthèsis = $this->getPositionsClosedParenthesis($formula);
                $i = 0;
                foreach($positionsClosedParenthèsis as $key => $positionClosedParenthèsis){
                    // si la taille de la formule est différente de la position de la parenthèse fermé
                    if (strlen($formula) - 1 !== $positionClosedParenthèsis) {
                        $i++;
                        $positionClosedParenthèsis = $positionClosedParenthèsis + $i;
                        if(strlen($formula) - 1 >= $positionClosedParenthèsis){
                            $formula = substr_replace($formula, " ", $positionClosedParenthèsis, 0);
                        }
                    }
                }
                $i = 0;
                $positionsOpenParenthèsis = $this->getPositionsOpenParenthesis($formula);
                foreach($positionsOpenParenthèsis as $key => $positionOpenParenthèsis){
                      // on ajoute un espace avant les parenthèses ouvertes dans la chaine
                    if($positionOpenParenthèsis != 0){
                        $i++;
                        $positionOpenParenthèsis = $positionOpenParenthèsis + $i;
                        $formula = substr_replace($formula, " ", $positionOpenParenthèsis - 1, 0);
                    }
                }
                

                $formula = explode(" ",$formula);
                $formula = array_filter($formula);

                $i = -1;
                $formula = array_values($formula);
                $formulaParenthèsis = [];
                $positionFormulaParenthèsis = [];
                foreach ($formula as $key => $item) {
                    if (strpos($item, "(") !== false) {
                        $positionFormulaParenthèsis[] = $key;
                        $formulaParenthèsis[] = $item;
                    }
                }

                foreach($formulaParenthèsis as $key => $item){ 
                        $i++;
                        $atomsInParenthèsis = $item;
                        $openParenthèsis = intval(implode("", $this->getPositionsOpenParenthesis($atomsInParenthèsis)));
                        $closedParenthèsis = intval(implode("", $this->getPositionsClosedParenthesis($atomsInParenthèsis)));

                        // on supprime les parenthèse de la mini formule récupérer entre parenthèse
                        $atomsInParenthèsis = substr_replace($atomsInParenthèsis, "", $openParenthèsis, 1);
                        $atomsInParenthèsis = substr_replace($atomsInParenthèsis, "", $closedParenthèsis - 1, 1);
                        
                        if (ctype_digit($atomsInParenthèsis[0]) === true){
                            return 'error';
                        }else{

                            $atomsInParenthèsis = $this->spaceOne($atomsInParenthèsis);
                            

                            $atomsInParenthèsis = $this->getAtoms($atomsInParenthèsis);
                        }
                        foreach($atomsInParenthèsis as $key2 => $atomAndNumber){
                            foreach ($atomAndNumber as $key3 => $atom) {
                                
                                if (preg_match('/[A-Z]/', $atom) !== 1) {
                                    $atom = intval($atom);
                                        if ($multiple[0] !== "" && $multiple[$key] !== "Vide"){
                                            $atom = $atomAndNumber[$key3] * $multiple[$key];
                                        }else{
                                            $atom = $atomAndNumber[$key3];
                                        }
                                    $atom = strval($atom);
                                    $atomAndNumber[$key3] = $atom;
                                    $atomsInParenthèsis[$key2] = implode("", $atomAndNumber);
                                }else{                                    
                                    $atomsInParenthèsis[$key2] = implode("", $atomAndNumber);
                                }
                                
                            }
                        }
                        if(count($atomAndNumber) < 2){
                            if ($multiple[$i] > 1 && $multiple[$i] !== "Vide") {
                                $atomAndNumber[] = $multiple[$i];
                                $atomsInParenthèsis[$key2] = implode("", $atomAndNumber);
                            }
                        }

                        $item = implode("" ,$atomsInParenthèsis);

                        $formula[$positionFormulaParenthèsis[$key]] = $item;
                }

                $formula = implode("", $formula);
                return $formula;
            }
            else{
                return false;
            }
        }

        public function positionCtypeUpper($formula){

            $positionAtome = [];
            $strlen = strlen($formula);
            /* on ajoute chaque position de la formule où se situe une majuscule dans un tableau et on vérifie qu'il n'y a aucun espace devant, 
            si c'est le cas alors on ne récupère pas la position et on ne l'ajoute pas dans le tableau */

            for($i = 0; $i < $strlen; $i++){
                if($i != 0){
                    $a = $i + 1;
                    $b = $i - 1;
                    if(ctype_upper($formula[$i]) == true){
                        if(strlen($formula) == $a){
                            if($formula[$b] != " "){
                                $positionAtome[] = $i;
                            }
                        }
                        else{
                            if($formula[$b] != " "){
                                $positionAtome[] = $i;
                            }
                        }
                    }
                    else if(ctype_lower($formula[$i]) == true && ctype_upper($formula[$b]) != true){
                        if(strlen($formula) == $a){
                            if($formula[$b] != " "){
                                $positionAtome[] = $i;
                            }
                        }
                        else{
                            if($formula[$b] != " "){
                                $positionAtome[] = $i;
                            }
                        }
                    }
                    
                }
            }

            return $positionAtome;
        }

        public function spaceOne($formula){
            $positionAtome = $this->positionCtypeUpper($formula);
            $newFormula = [];
            /* on traite alors le tableau qui contient les positions et 
            on ajoute un espace juste devant chaque postion dans la formule */
            $a = 0;
            if ($positionAtome) {
                for ($i=0; $i < count($positionAtome); $i++) {
                    if (empty($newFormula)) {
                        $newFormula[] = substr_replace($formula, ' ', $positionAtome[$i], 0);
                    } else {
                        $a++;
                        $positionAtome[$i] = $positionAtome[$i] + $a;
                        $newFormula[0] = substr_replace($newFormula[0], ' ', $positionAtome[$i], 0);
                    }
                }
            }else{
                $newFormula[] = $formula;
            }
            return $newFormula;
    }


    public function calculateOne($formula){  
        
        if($this->cleanFormula($formula) !== false){
            $formula = $this->cleanFormula($formula);
        }
        if ("error" !== $formula) {
            $formula = $this->spaceOne($formula);
            if ($this->deleteDoublon($formula) != false) {
                $formula = $this->deleteDoublon($formula);
            }
            $tabAtomNumber = $this->getAtoms($formula);
            $massAtomsNumberByFormula = [];
        }
        else{
            $tabAtomNumber = [];
            $massAtomsNumberByFormula = [];
        }
        
        // On vérife d'abord si la première occurence de la formule n'est pas un nombre.
        if(ctype_digit(explode(" ",$formula[0])[0])){
            $massAtomsNumberByFormula = [];
        }else{
            // $tabAtomNumber = $this->deleteDoublon($tabAtomNumber);
            foreach ($tabAtomNumber as $key => $atom) {
                
                if (strlen($atom[0]) > 1) {
                    $firstLetter = substr($atom[0], 0, 1);
                    if (ctype_upper($firstLetter) == true) {
                        $atomDb = $this->atomRepo->findOneBy(['name' => $atom[0]]);
                        if ($atomDb != null) {
                            
                            if (count($atom) > 1) {
                                $massAtomsNumberByFormula[] = $atomDb->getMass() * $atom[1];
                            } else {
                                $massAtomsNumberByFormula[] = floatval($atomDb->getMass());
                            }
                        }
                    } else {
                        $massAtomsNumberByFormula = [];
                    }
                } else {
                    if (ctype_upper($atom[0]) == true) {
                        $atomDb = $this->atomRepo->findOneBy(['name' => $atom[0]]);

                        if ($atomDb != null) {
                            if (count($atom) > 1) {
                                $massAtomsNumberByFormula[] = $atomDb->getMass() * $atom[1];
                            } else {
                                $massAtomsNumberByFormula[] = floatval($atomDb->getMass());
                            }
                        } else {
                            $massAtomsNumberByFormula = [];
                        }
                    } else {
                        $massAtomsNumberByFormula = [];
                    }
                }
            };
            
        }        

        if($massAtomsNumberByFormula && count($massAtomsNumberByFormula) === count($tabAtomNumber)){
            return round(array_sum($massAtomsNumberByFormula), 4);
        }else{
            return 'error';
        } 
       
    }
    
   public function getAtoms($formula){

    $formula = implode("", $formula);

    $formula = explode(' ', $formula);

    $tabAtomNumber = [];
        foreach($formula as $atomNumber){
            $atomSplit = str_split($atomNumber, 1);
            foreach($atomSplit as $index2 => $i){
                    if(is_numeric($i)){
                        $indexMoinsUn = $index2 - 1;
                        if(!is_numeric($atomNumber[$indexMoinsUn])){ 
                            $atomAndNumber = substr_replace($atomNumber,' ', $index2, 0);
                            $tabAtomNumber[] = explode(' ',$atomAndNumber);
                        }
                    }
            }
            if(!preg_match('~[0-9]+~', $atomNumber)){ 
                $tabAtomNumber[] = array($atomNumber);
            }
        }

        return $tabAtomNumber;
   }

    public function deleteDoublon($formula){
        $tabAtomNumber = $this->getAtoms($formula);

        $sameAtom = [];
        foreach ($tabAtomNumber as $key => $atom) {
            
            for ($i = 0; $i < count($tabAtomNumber); $i++) {
                if ($i !== $key) {
                    
                    if ($atom[0] === $tabAtomNumber[$i][0]) {
                        
                        if (in_array($atom[0], $sameAtom) !== true) {
                            $sameAtom[] = $atom[0];
                        }                       
                    }
                }  
            }  
        }
        $array = [];
        $array2 = [];
        if(empty($sameAtom)){
            return false;
        }else{
            foreach ($sameAtom as $key => $atom) {
                $array2[] = $array;
            }

            foreach ($tabAtomNumber as $key => $atom) {
                foreach ($array2 as $key2 => $array) {
                    if ($atom[0] === $sameAtom[$key2]) {
                        if (empty($array)) {
                            $array2[$key2][] = $atom;
                        } else {
                            $array2[$key2][] = $atom;
                        }
                    }
                }
            }
            foreach ($array2 as $key => $array) {
                foreach ($array as $key2 => $atom) {
                        // On vérifie si l'atom comporte un nombre avec lui sinon il est isolé.
                        if (count($atom) > 1) {
                            // On vérifie si dans le tableau des atoms commun, 
                            // celui qui est a la position 0 est un tableau avec l'atom et son nombre.
                            if (count($array2[$key][0]) > 1) {
                                // On ajoute uniquement les nombres des atoms dont l'atom à une clé supérieur à 0 
                                // et on l'ajoute au nombre qui concerne l'atom qui à sa clé à 0. 
                                if ($key2 != 0) {
                                    $array2[$key][0][1] = $atom[1] + $array2[$key][0][1];
                                }
                            } else {
                                $array2[$key][0][] = $atom[1] + 1;
                            }
                        }else{
                            if (count($array2[$key][0]) > 1) {
                                $array2[$key][0][1] = 1 + $array2[$key][0][1];
                            } else {
                                $array2[$key][0][] = 1;
                            }
                        }
                }
                $array2[$key] = $array2[$key][0];  
                // On vérifie si le dans le tableau atom il y a un nombre qu'il lui est associé
                if (count($array2[$key]) > 1) {
                    $array2[$key][1] = strval($array2[$key][1]);
                }
            }

            $tabAtom = [];
            $tabAtomSame = [];
            foreach ($array2 as $key => $atom) {
            
                for ($i = 0; $i < count($tabAtomNumber); $i++) {
                    if ($atom[0] === $tabAtomNumber[$i][0]) {
                        $tabAtomSame[] = implode(" ",$tabAtomNumber[$i]);
                    }
                    if(in_array(implode(" ", $tabAtomNumber[$i]), $tabAtom) !== true){
                        $tabAtom[] = implode(" ",$tabAtomNumber[$i]);
                    }
                }
            };
            $tabAtomNumber = array_diff($tabAtom, $tabAtomSame);
            $tabAtomNumber = array_values($tabAtomNumber);
            foreach ($tabAtomNumber as $key => $atom) {
                $tabAtomNumber[$key] = explode(' ', $atom);
                $tabAtomNumber[$key] = implode('', $tabAtomNumber[$key]);  
            }
            $array2 = array_reverse($array2);
            foreach($array2 as $atomNumber){
                array_unshift($tabAtomNumber, implode("", $atomNumber));
            }
            $formula = implode(' ', $tabAtomNumber);
            $formula = [$formula];
            return $formula;
        }
    }
}