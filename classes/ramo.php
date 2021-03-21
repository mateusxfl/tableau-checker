<?php

    class Ramo {

        private $raiz;

        // Adiciona uma hipótese em um ramo.
        function adicionarHipoteses($status, $sequente) {
            
            if($this->raiz == NULL) {
                $this->raiz = new No($status, $sequente);
            }else{

                // Busca o último sequente de um ramo.
                $final = $this->buscarUltimoSequente();

                // Insere um novo nó no fim do ramo.
                $final->proximo = new No($status, $sequente);
                
            }

        }

        // Expande o ramo passado por parâmetro.
        function expandirRamo(){

            // Importa a pilha de ramos a serem executados.
            global $pilhaDeRamos;

            // Insere o primeiro ramo na pilha de ramos.
            array_push($pilhaDeRamos, $this);

            // Executa enquanto existir ramos na pilha.
            while(count($pilhaDeRamos) != 0) {     
                
                // Escolhe o último ramo inserido na pilha.
                $ramoAtual = end($pilhaDeRamos);

                // Busca qual sequente deve ser expandido.
                $expandir = $ramoAtual->checarPrioridade();

                // echo $expandir->status."".$expandir->sequente."<br>";

                // Preenche um vetor com todos os átomos marcados do ramo atual.
                $atomosMarcados = $ramoAtual->buscarAtomosMarcados();

                // Busca o último nó do ramo atual.
                $ultimoSequente = $this->buscarUltimoSequente();

                // Ramo atual é saturado,  (não dá mais pra expandir).
                if(!is_object($expandir)) {

                    // Imprime todos os átomos marcados (valoração que insatisfaz a fórmula).
                    echo "Valoração que insatisfaz a fórmula: ".join($atomosMarcados," ; ")."<br><br>";

                    // Retira ramo da pilha, para o próximo ramo ser executado.
                    array_pop($pilhaDeRamos);

                }else{

                    // Marca o sequente como expandido, para não interferir na ordem de expanção.
                    $expandir->expandido = true;

                    if($expandir->tipo == "ALFA") {

                        if(isset($expandir->filho1)) {

                            // O último nó do ramo vai receber o nó referente ao 1° filho.
                            $ultimoSequente->proximo = $expandir->filho1;

                            // Avança uma posição no sequente.
                            $ultimoSequente = $ultimoSequente->proximo;

                            // Apaga o seu filho, tendo em vista que o mesmo foi usado.
                            unset($expandir->filho1); 

                        }

                        if(isset($expandir->filho2)) {
                            
                            // O último nó do ramo vai receber o nó referente ao 2° filho.
                            $ultimoSequente->proximo = $expandir->filho2;
                            
                            // Apaga o seu filho, tendo em vista que o mesmo foi usado.
                            unset($expandir->filho2);

                        }

                    }else if($expandir->tipo == "BETA") {

                        // Cria uma bifurcação do ramo atual (faz uma cópia do objeto ramo).
                        $novoRamo = unserialize(serialize($ramoAtual));

                        // Estou criando o objeto novamente, devo apenas copiar o objeto já existente.
                        $ramoAtual->adicionarHipoteses($expandir->filho1->status, $expandir->filho1->sequente);
                        $novoRamo->adicionarHipoteses($expandir->filho2->status, $expandir->filho2->sequente);

                        array_push($pilhaDeRamos, $novoRamo);
                                                
                    }else if($expandir->tipo == "SATURADO") {

                        // Verifica se o ramo pode ser fechado.
                        $ramoAtual->fecharRamo($expandir, $atomosMarcados);

                    }

                }
            }

            echo "Árvore expandida. <br>";
                
        }

        // Busca ultímo sequente de um ramo.
        private function buscarUltimoSequente() {

            $noAtual = $this->raiz;
                
            // Busca o último nó do ramo.
            while($noAtual->proximo != NULL) {
                $noAtual = $noAtual->proximo;
            }

            // Retorna o último nó do ramo.
            return $noAtual;

        }

        // Busca todos os átomos marcados de um ramo.
        private function buscarAtomosMarcados() {

            // Cria um vetor para armazenar os átomos marcados.
            $atomosMarcados = array();

            $noAtual = $this->raiz;

            // Pecorre todos os nó do ramo.  
            while($noAtual != NULL) {

                // Se o átomo é saturado, armazena no vetor de atomos marcados.
                if($noAtual->tipo == "SATURADO" && $noAtual->expandido == 1){

                    // Atribui o valoração referente ao status.
                    $valoracao = $noAtual->status == "T" ? 1 : 0;

                    // Átomo que vai ser inserido no array de fórmulas marcadas.
                    $atomoInserido = $noAtual->sequente." = ".$valoracao;

                    // Condição para não inserir valores repetidos no array de átomos marcados.
                    if(!in_array($atomoInserido, $atomosMarcados)) {
                        array_push($atomosMarcados, $atomoInserido);
                    }

                }

                // Busca o proximo nó do ramo.
                $noAtual = $noAtual->proximo;

            }

            // Retorna o vetor com todos os átomos marcados do ramo.
            return $atomosMarcados;

        }

        // Verifica qual sequente do ramo deve ser expandido.
        private function checarPrioridade() {

            // Valor default da prioridade, assumindo que todos os sequentes já foram expandidos.
            $prioridade = "Todos os sequentes foram expandidos.";

            $noAtual = $this->raiz;

            // Percorre todos os nó do ramo.  
            while($noAtual != NULL) {

                // Caso o nó ainda não tenha sido expandido.
                if(!$noAtual->expandido) {

                    if($noAtual->tipo == "SATURADO") {
                        return $noAtual;
                    }else if($noAtual->tipo == "ALFA" && (!is_object($prioridade) || $prioridade->tipo == "BETA")) {
                        $prioridade = $noAtual;
                    }else if(!is_object($prioridade)) {
                        $prioridade = $noAtual;
                    }

                }

                // Busca o proximo nó do ramo.
                $noAtual = $noAtual->proximo;
                
            }
            
            // Retorna o ramo (objeto) que deve ser expandido.
            return $prioridade;

        } 

        // Verifica se um ramo pode ser fechado.
        private function fecharRamo($no, $atomosMarcados) {

            // Importa a pilha de ramos a serem executados.
            global $pilhaDeRamos;

            // Atribui o valoração referente ao status.
            $valoracao = $no->status == "T" ? 1 : 0;

            // Define o átomo marcado a ser buscado.
            $buscado = $no->sequente." = ".$valoracao;

            // Reverso do átomo marcado a ser buscado.
            if($valoracao){
                $buscadoReverso = $no->sequente." = 0";
            }else{
                $buscadoReverso = $no->sequente." = 1";
            }

            // Se o atomo buscado está no vetor de átomos marcados, fechar ramo.
            if(in_array($buscadoReverso, $atomosMarcados)) {
                
                // Retorna mensagem.
                echo "Ramo fechado. ".join($atomosMarcados," ; ")." ; $buscado <br><br>";

                // Retira ramo da pilha, para o próximo ser executado.
                array_pop($pilhaDeRamos);
                
            }

        }
        
    }

?>