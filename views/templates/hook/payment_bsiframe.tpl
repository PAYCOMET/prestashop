{*
    * 2007-2015 PrestaShop
    *
    * NOTICE OF LICENSE
    *
    * This source file is subject to the Academic Free License (AFL 3.0)
    * that is bundled with this package in the file LICENSE.txt.
    * It is also available through the world-wide-web at this URL:
    * http://opensource.org/licenses/afl-3.0.php
    * If you did not receive a copy of the license and are unable to
    * obtain it through the world-wide-web, please send an email
    * to license@prestashop.com so we can send you a copy immediately.
    *
    * DISCLAIMER
    *
    * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
    * versions in the future. If you wish to customize PrestaShop for your
    * needs please refer to http://www.prestashop.com for more information.
    *
    *  @author     PAYCOMET <info@paycomet.com>
    *  @copyright  2019 PAYTPV ON LINE ENTIDAD DE PAGO S.L
    *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
    *}


    <div class="row">
        {if ($newpage_payment==1)}
            <div class="">
        {else}
            <div class="">
        {/if}
            <div class="paytpv">

                {if ($paytpv_integration==1)}
                    <form action="{$paytpv_jetid_url|escape:'htmlall':'UTF-8':FALSE}" method="POST" class="paytpv_jet" id="paycometPaymentForm" style="clear:left;">
                {/if}

                {if ($msg_paytpv!="")}
                <p>
                    <span class="message">{$msg_paytpv|escape:'htmlall':'UTF-8':FALSE}</span>
                </p>
                {/if}
                {if ($active_suscriptions)}
                    {include file='modules/paytpv/views/templates/hook/inc_payment_suscription.tpl'}
                {/if}

                {if ($newpage_payment==1)}
                    <p class="operation_data">
                        <div class="pad">
                          <div style="display:inline-table;">
                            <div class="operation">
                                <h4 class="cost_num">{l s='Total Amount' mod='paytpv'}: <b>{$total_amount|escape:'htmlall':'UTF-8':FALSE} {$currency_symbol|escape:'htmlall':'UTF-8':FALSE}</b></h4>
                            </div>
                          </div>
                        </div>
                  </p>
                {/if}


                <div id="saved_cards" style="display:none">
                    {include file='modules/paytpv/views/templates/hook/inc_payment_cards.tpl'}

                    {if (sizeof($saved_card)>1)}
                        <div id="button_directpay" style="margin-top:10px;">
                            <button id="exec_directpay" href="#" class="btn btn-primary center-block exec_directpay paytpv_pay">
                                <span>{l s='Pay' mod='paytpv'}<i class="icon-chevron-right right"></i></span>
                            </button>
                            <img id='clockwait' style="display:none" src="{$base_dir|escape:'htmlall':'UTF-8':FALSE}modules/paytpv/views/img/clockpayblue.gif"></img>
                        </div>
                    {/if}

                </div>

                <div id="paytpv_checkconditions" style="display:none">
                    <strong>{l s='You must accept the license terms to continue' mod='paytpv'}</strong>
                </div>

                {if (!$disableoffersavecard==1)}
                    {include file='modules/paytpv/views/templates/hook/inc_payment_savecards.tpl'}
                {/if}

                <br class="clear"/>

                <div class="payment_module paytpv_iframe" style="display:none">

                    {if ($newpage_payment==1)}
                        <div class="info_paytpv">
                          <p>{l s='The input data is stored on servers in PAYCOMET company with PCI / DSS Level 1 certification, making payments 100% secure.' mod='paytpv'}</p>
                        </div>
                    {/if}


                    {if ($newpage_payment<2)}

                        {if ($paytpv_integration==0)}
                            <p id='ajax_loader' style="display:none">
                                <img id='ajax_loader' src="{$base_dir|escape:'htmlall':'UTF-8':FALSE}modules/paytpv/views/img/clockpayblue.gif"></img>
                                {l s='Loading payment form...' mod='paytpv'}
                            </p>
                            <iframe id="paytpv_iframe" src="{$paytpv_iframe|escape:'htmlall':'UTF-8':FALSE}" name="paytpv" style="width: 98%; border-top-width: 0px; border-right-width: 0px; border-bottom-width: 0px; border-left-width: 0px; border-style: initial; border-color: initial; border-image: initial; height: {$iframe_height|escape:'htmlall':'UTF-8':FALSE}px;" marginheight="0" marginwidth="0" scrolling="no" sandbox="allow-top-navigation allow-scripts allow-same-origin allow-forms"></iframe>
                        {else}
                            {include file='modules/paytpv/views/templates/hook/inc_payment_jetIframe.tpl'}
                        {/if}


                        {if ($newpage_payment==1)}
                        <div class="paytpv_footer">

                            <div class="paytpv_wrapper mobile">
                                <div class="footer_line">
                                  <ul class="payment_icons">
                                    <li><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/visa.png" alt="Visa"></li>
                                    <li><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/visa_electron.png" alt="Visa Electron"></li>
                                    <li><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/mastercard.png" alt="Mastercard"></li>
                                    <li><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/maestro.png" alt="Maestro"></li>
                                    <li><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/amex.png" alt="American Express"></li>
                                    <li><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/jcb.png" alt="JCB card"></li>
                                    <li><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/veryfied_by_visa.png" alt="Veryfied by Visa"></li>
                                    <li><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/mastercard_secure_code.png" alt="Mastercard Secure code"></li>
                                    <li><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/pci.png" alt="PCI"></li>
                                    <li><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/thawte.png" alt="Thawte"></li>
                                  </ul>
                                </div>
                            </div>
                        </div>
                        {/if}
                    {/if}
                </div>

                {if ($paytpv_integration==1)}
                    </form>
                {/if}

            </div>
    </div>

    <div class="modal fade" id="modal-paytpv">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <div class="js-modal-content"></div>
          </div>
        </div>
    </div>

    <input type="hidden" name="paytpv_module" id="paytpv_module" value="{$paytpv_module|escape:'htmlall':'UTF-8':FALSE}">
    <input type="hidden" name="newpage_payment" id="newpage_payment" value="{$newpage_payment|escape:'htmlall':'UTF-8':FALSE}">
    <input type="hidden" name="paytpv_integration" id="paytpv_integration" value="{$paytpv_integration|escape:'htmlall':'UTF-8':FALSE}">

    <form id="form_paytpv" action="{$base_dir|escape:'htmlall':'UTF-8':FALSE}index.php?controller=order" method="post">
        <input type="hidden" name="step" value="3">
        <input type="hidden" name="paytpv_cc" id="paytpv_cc" value="">

        <input type="hidden" name="paytpv_agree" id="paytpv_agree"  value="0">
        <input type="hidden" name="action_paytpv" id="action_paytpv"  value="">

        <!--SUSCRIPCIONES-->
        <input type="hidden" name="paytpv_suscripcion" id="paytpv_suscripcion"  value="0">
        <input type="hidden" name="paytpv_periodicity" id="paytpv_periodicity"  value="0">
        <input type="hidden" name="paytpv_cycles" id="paytpv_cycles"  value="0">

        <input type="hidden" name="id_cart" id="id_cart"  value="{$id_cart|escape:'htmlall':'UTF-8':FALSE}">

    </form>

</div>
