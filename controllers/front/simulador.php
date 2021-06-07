<?php

class paytpvSimuladorModuleFrontController extends ModuleFrontController
{

    public function initContent()
    {
        $urlSimulador = "https://instantcredit.net/simulator/test/ic-simulator.js";

        parent::initContent();

        $importe_financiar = Tools::getValue('importe_financiar');
        $hashToken = Configuration::get('PAYTPV_APM_instant_credit_hashToken');

        if (isset($importe_financiar)) {
            $importe_financiar = str_replace(",", ".", $importe_financiar);
            $simulador = "<html>
                            <body>
                             <div class=\"ic-configuration\" style=\"display:none;\">" . $hashToken . "</div>
                                <div class=\"ic-simulator\" amount=\"" . $importe_financiar . "\"></div>
                            </body>
                            <script src=\"" . $urlSimulador . "\"></script>
                         </html>";
            header_remove();
            header("content-type: text/html");
            die($simulador);
        }
    }
}
