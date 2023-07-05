<?php 

namespace Systrio\Plugins\Hikabarr\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\RadioField;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Systrio\Plugins\Hikabarr\Dolibarr\RequestApi;

defined('_JEXEC') or die;

class VerifconnectionField extends FormField
{

    public $type = 'verifconnection';

    public function getPluginParams()
    {
        $plugin = PluginHelper::getPlugin('hikashop', 'hikabarr');
        if (empty($plugin->params)) return;

        return json_decode($plugin->params);
    }
    
    public function getInput()
    {
        if (empty($this->getPluginParams()->api_url)) return '';
        if (empty($this->getPluginParams()->api_key)) return '';

        $request = new RequestApi($this->getPluginParams()->api_url, $this->getPluginParams()->api_key);
        $result = (!is_bool($request->get('status'))) ? $request->get('status') : '""';

        $app = Factory::getApplication();
        $wa = $app->getDocument()->getWebAssetManager();
        
        $wa->addInlineScript('
            window.onload = () => {
                let button_verif = document.querySelector("#btn-verif-connection");
                let div = document.createElement("div");
                div.classList.add("mt-4");
                div.classList.add("div-verif-connection");

                button_verif.parentElement.append(div);

                button_verif.addEventListener("click", (e) => {
                    e.preventDefault();
                    let result = ' . $result . ';
                    div.innerHTML = "";

                    let divAlert = document.createElement("div");
                    divAlert.classList.add("alert");
                    divAlert.setAttribute("role", "alert");

                    let p = document.createElement("p");
                    p.classList.add("mb-0");

                    if (result.success && result.success.code == 200)
                    {
                        divAlert.classList.add("alert-success");
                        p.textContent = "' . Text::_('PLG_HIKABARR_CONNECTION_ALERT_SUCCESS') . '";
                    }

                    if (result.error)
                    {
                        divAlert.classList.add("alert-danger");
                        p.textContent = "' . Text::_('PLG_HIKABARR_CONNECTION_ALERT_DANGER') . '";
                    }

                    divAlert.append(p);
                    div.append(divAlert);
                });
            }
        ');

        return '<button class="btn btn-success" id="btn-verif-connection">' . Text::_('PLG_HIKABARR_PARAMS_FIELDS_BUTTON_VERIF') . '</button>';
    }

    public function getLabel()
    {
        if (empty($this->getPluginParams()->api_url)) return '';
        if (empty($this->getPluginParams()->api_key)) return '';

        return parent::getLabel();
    }

}