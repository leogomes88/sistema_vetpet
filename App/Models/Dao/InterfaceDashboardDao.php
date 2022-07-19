<?php

    namespace App\Models\Dao;

    interface InterfaceDashboardDao {        

        public function idadeDoSistema();
        public function dadosMes($mes_referencia);
    }

?>