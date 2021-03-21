<?php

    // Array com os símbolos atômicos.
    $simbolosAtomicos = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
        
    // Array com os conectivos.
    $conectivos = array('&','#','>');

    // Array com os conectivos unários.
    $conectivosUnarios = array('~');

    // Array com os símbolos auxiliares.
    $simbolosAuxiliares = array('(',')');

    // Pilha de execução que armazena todos os ramos a serem executados.
    $pilhaDeRamos = array();

    // Classe que representa um nó dentro de um ramo.  
    class No {

        public $status, $sequente, $expandido, $tipo, $proximo;

        // Construtor da classe.
        public function __construct($status, $sequente) {

            // $this->padronizarParenteses($sequente);

            if(!$this->validarAlfabeto($sequente)) {
                echo $sequente." tem o alfabeto inválido, o mesmo não foi adicionado ao ramo. <br><br>"; return 0;
            }

            if(!$this->validarSintaxe($sequente)) {
                echo $sequente." tem a sintaxe inválida, o mesmo não foi adicionado ao ramo. <br><br>"; return 0;
            }
            
            $this->status = $status;
            $this->sequente = $sequente;
            $this->expandido = false;
            $this->proximo = NULL;   

            // Insere o tipo do sequente, assim como suas fórmulas geradas.
            $this->checarTipo($sequente);

        }

        // Transforma um vetor em uma string.
        private function arrayToString($vetor){

            $string = "";

            for( $i = 0 ; $i < count($vetor) ; $i++ ) {
                $string .= $vetor[$i];
            }

            return $string;

        }

        // Usa um regex para validar o alfabeto de um sequente.
        private function validarAlfabeto($sequente) {

            $sequente = str_replace(' ', '', $sequente);

            $padrao = "/^[a-z&\#\>\~\)\(]+$/";

            if(preg_match($padrao, $sequente)) {
                return 1;
            }else{
                return 0;
            }

        }

        // Verifica se a sintaxe de um sequente é válida.
        private function validarSintaxe($sequente){

            // Importo todas as variáveis globais para a função.
            global $simbolosAtomicos, $conectivos, $conectivosUnarios, $simbolosAuxiliares;

            $sequente = str_replace(' ', '', $sequente);

            $sequente = str_split($sequente);
            
            /**
             * Verifica a primeira posição quando há somente um caractere na fórmula, caso o único 
             * caractere não seja um símbolo atômico, a fórmula é inválida.
             */
            $primeiraPosicaoUmCaractere = count($sequente) == 1 && !in_array($sequente[0], $simbolosAtomicos);

            /**
             * Verifica a primeira posição quando há mais de um caractere na fórmula, caso o primeiro 
             * caractere não seja um símbolo atômico, uma negação ou um ( , a fórmula é inválida.
             */
            $primeiraPosicaoMaisDeUmCaractere = count($sequente) > 1 && !in_array($sequente[0], $simbolosAtomicos) && !in_array($sequente[0], $conectivosUnarios) && $sequente[0] != "(";

            /**
             * Verifica a última posição quando há mais de um caractere na fórmula, caso o primeiro 
             * caractere não seja um símbolo atômico ou um ) , a fórmula é inválida.
             */
            $ultimaPosicaoMaisDeUmCaractere = count($sequente) > 1 && !in_array($sequente[(count($sequente)-1)], $simbolosAtomicos) && $sequente[(count($sequente)-1)] != ")";

            // Verifica as três condições definidas anteriormente.
            if($primeiraPosicaoUmCaractere || $primeiraPosicaoMaisDeUmCaractere || $ultimaPosicaoMaisDeUmCaractere) {
                return false;
            }

            // Recorre todo o sequente verificando seu caractere posterior.
            for ( $i = 0 ; $i < count($sequente) ; $i++ ) {
                
                if(in_array($sequente[$i], $simbolosAuxiliares)){

                    $counter = 0;

                    $j = $i;

                    if($sequente[$i] == "(") {

                        // O próximo caractere só pode ser um símbolo atômico, uma negação ou ( .
                        if(isset($sequente[$i+1])){
                            if(!in_array($sequente[$i+1], $simbolosAtomicos) && !in_array($sequente[$i+1], $conectivosUnarios) && $sequente[$i+1] != "(") {
                                return false;
                            }
                        }

                        // Verifica se esse parêntese fecha corretamente.
                        do{
                            if($sequente[$j] == "(") {
                                $counter++;
                            }else if($sequente[$j] == ")") {
                                $counter--;
                            }
        
                            $j++;
                        }while($counter != 0 && $j < count($sequente));

                    }else if($sequente[$i] == ")") {
                        
                        // O próximo caractere só pode ser um conectivo ou ) .
                        if(isset($sequente[$i+1])) {
                            if(!in_array($sequente[$i+1], $conectivos) && $sequente[$i+1] != ")") {
                                return false;
                            }
                        }

                        // Verifica se esse parêntese fecha corretamente.
                        do{
                            if($sequente[$j] == "(") {
                                $counter--;
                            }else if($sequente[$j] == ")") {
                                $counter++;
                            }
        
                            $j--;
                        }while($counter != 0 && $j >= 0);

                        /**
                         * Verifico se fecha voltando pelo fato de que poderia ter uma fórmula (a > b)),
                         * que iria validar, pois verificou somente os parênteses indo.
                         */
        
                    }

                    /** 
                     * Após a verificação se dado parêntese ) ou ( , é verificado se o mesmo fecha
                     * corretamente, se ele não fechar corretamente, $counter será diferente de 0.
                    */
                    if($counter != 0) {
                        return false;
                    }

                }else if(in_array($sequente[$i], $simbolosAtomicos)) {

                    // O próximo caractere só pode ser um conectivo ou ) .
                    if(isset($sequente[$i+1])){
                        if(!in_array($sequente[$i+1], $conectivos) && $sequente[$i+1] != ")") {
                            return false;
                        }
                    }

                }else if(in_array($sequente[$i], $conectivos)) {

                    // O próximo caractere só pode ser um símbolo atômico, uma negação ou ( .
                    if(isset($sequente[$i+1])) {
                        if(!in_array($sequente[$i+1], $simbolosAtomicos) && !in_array($sequente[$i+1], $conectivosUnarios) && $sequente[$i+1] != "(") {
                            return false;
                        }
                    }

                }else if(in_array($sequente[$i], $conectivosUnarios)) {

                    // O próximo caractere só pode ser um símbolo atômico, uma negação ou ( .
                    if(isset($sequente[$i+1])) {
                        if(!in_array($sequente[$i+1], $simbolosAtomicos) && !in_array($sequente[$i+1], $conectivosUnarios) && $sequente[$i+1] != "(") {
                            return false;
                        }
                    }

                }

            }

            // Caso nenhuma das restrições tenha retornado false, a fórmula é válida, retornando true.
            return true;

        }

        // Padroniza os parênteses de um sequente.
        private function padronizarParenteses($sequente) {
            // Fazer.
        }

        // Verifica se um sequente é BETA ou ALFA.
        private function checarTipo($sequente) {

            $sequente = str_replace(' ', '', $sequente);

            $arraySequente = str_split($sequente);

            global $conectivos;

            if($arraySequente[0] == "(") {

                $counter = 0; $j = 0;

                // Retona o índice do parêntese que fecha com o primeiro.
                do{ 

                    if($arraySequente[$j] == "(") {
                        $counter++;
                    }else if($arraySequente[$j] == ")") {
                        $counter--;
                    }

                    $j++;

                }while($counter != 0 && $j < count($arraySequente));

                // Tem outra fórmula após o parentese de fechamento.
                if(isset($arraySequente[$j+1]) && in_array($arraySequente[$j], $conectivos)) { 
                
                    // Captura a primeira parte da fórmula (antes do conectivo).
                    $formulaAntes = substr($this->arrayToString($arraySequente), 0, ($j)); 

                    // Captura a segunda parte da fórmula (depois do conectivo).
                    $formulaDepois = substr($this->arrayToString($arraySequente), ($j + 1)); 
                    
                    // Captura o conectivo que une as duas fórmulas anteriores.
                    $conectivo = $arraySequente[$j]; 

                    // Atribuo T ou F as novas fórmulas geradas.
                    $this->atribuirTF($formulaAntes, $formulaDepois, $conectivo);

                }else { 
                    
                    // Chama a função novamente com a fórmula sem os parênteses externos.
                    $this->checarTipo(substr($this->arrayToString($arraySequente), 1, -1));

                }

            }

            // Atômico (sem parêntese), exemplo: a > b.
            else if(isset($arraySequente[1]) && in_array($arraySequente[1], $conectivos)) { 

                // Captura a primeira parte da fórmula (antes do conectivo).
                $formulaAntes = substr($this->arrayToString($arraySequente), 0, 1); 

                // Captura a segunda parte da fórmula (depois do conectivo).
                $formulaDepois = substr($this->arrayToString($arraySequente), 2); 

                // Captura o conectivo que une as duas fórmulas anteriores.
                $conectivo = $arraySequente[1]; 

                // Atribuo T ou F as novas fórmulas geradas.
                $this->atribuirTF($formulaAntes, $formulaDepois, $conectivo);

            }
            
            // Chama a função novamente, trocando o status e removendo a negação.
            else if($arraySequente[0] == "~") { 

                $statusFormula = $this->status;

                $statusFormula = $statusFormula == "T" ? "F" : "T";

                $this->tipo = "ALFA";

                $this->filho1 = new No($statusFormula, substr($this->arrayToString($arraySequente), 1));

            }

            // Atômico.
            else if(count($arraySequente) == 1) { 
                $this->tipo = "SATURADO";
            }

        }

        // Atribui T ou F aos dois sequentes gerados, assim como também insere os sequentes gerados.
        private function atribuirTF($formulaAntes, $formulaDepois, $conectivo){

            // Verifica se $status $formulaAntes $conectivo $formulaDepois é ALFA ou BETA.
            if($this->status == "T") {
                if($conectivo == "&") { // TT ALFA
                    $s1 = "T"; $s2 = "T"; $tipoFormula = "ALFA";
                }else if($conectivo == "#") { // T/T BETA
                    $s1 = "T"; $s2 = "T"; $tipoFormula = "BETA";
                }else if($conectivo == ">") { // F/T BETA
                    $s1 = "F"; $s2 = "T"; $tipoFormula = "BETA";
                }
            }else if($this->status == "F") {
                if($conectivo == "&") { // F/F BETA
                    $s1 = "F"; $s2 = "F"; $tipoFormula = "BETA";
                }else if($conectivo == "#") { // FF ALFA
                    $s1 = "F"; $s2 = "F"; $tipoFormula = "ALFA";
                }else if($conectivo == ">") { // T/F ALFA
                    $s1 = "T"; $s2 = "F"; $tipoFormula = "ALFA";
                }
            }

            // Atribui o tipo da fórmula ao nó.
            $this->tipo = $tipoFormula;

            // Atribui as fórmulas geradas pelo sequente ao seu respectivo nó.
            $this->filho1 = new No($s1, $formulaAntes);
            $this->filho2 = new No($s2, $formulaDepois);

        }

    }  

?>