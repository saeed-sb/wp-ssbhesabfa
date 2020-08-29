<?php

/*
 * @package    ssbhesabfa
 * @subpackage ssbhesabfa/includes
 * @author     Saeed Sattar Beglou <saeed.sb@gmail.com>
 */

class Ssbhesabfa_Webhook
{
    public function __construct()
    {

        $hesabfaApi = new Ssbhesabfa_Api();
        $lastChange = get_option('ssbhesabfa_last_log_check_id');
        $changes = $hesabfaApi->settingGetChanges($lastChange + 1);
        if ($changes->Success) {
            foreach ($changes->Result as $item) {
                switch ($item->ObjectType) {
                    case 'Invoice':
                        $this->setInvoiceChangesById($item->ObjectId);
                        break;
                    case 'Product':
                        //if Action was deleted
                        if ($item->Action == 53) {
                            $id_obj = Ssbhesabfa_Admin_Functions::getObjectIdByCode('product', $item->Extra);
                            global $wpdb;
                            $wpdb->delete($wpdb->prefix . 'ssbhesabfa', array('id' => $id_obj));
//                            $hesabfa = new HesabfaModel($id_obj);
//                            $hesabfa->delete();
                        }
                        $this->setItemChangesById($item->ObjectId);
                        break;
                    case 'Contact':
                        //if Action was deleted
                        if ($item->Action == 33) {
                            $id_obj = Ssbhesabfa_Admin_Functions::getObjectIdByCode('customer', $item->Extra);
                            global $wpdb;
                            $wpdb->delete($wpdb->prefix . 'ssbhesabfa', array('id' => $id_obj));
//                            $hesabfa = new HesabfaModel($id_obj);
//                            $hesabfa->delete();
                        }
                        $this->setContactChangesById($item->ObjectId);
                        break;
                }
                $lastChange = $item->Id;
            }

            //set LastChange ID
            update_option('ssbhesabfa_last_log_check_id', $lastChange);
        } else {
            Ssbhesabfa_Admin_Functions::log(array("ssbhesabfa - Cannot check last changes. Error Message: " . (string)$changes->ErrorMessage . ". Error Code: " . (string)$changes->ErrorCode));
        }
    }

    // use in webhook call when invoice change
    public function setInvoiceChangesById($id)
    {
        $hesabfaApi = new Ssbhesabfa_Api();
        $invoice = $hesabfaApi->invoiceGetById($id);
        if ($invoice->Success && !empty($invoice->Result)) {
            //1.set new Hesabfa Invoice Code if changes
            $number = $invoice->Result->Number;
            $json = json_decode($invoice->Result->Tag);
            if (is_object($json)) {
                $id_order = $json->id_order;
            } else {
                $id_order = 0;
            }

            if ($invoice->Result->InvoiceType == 0) {
                //check if Tag not set in hesabfa
                if ($id_order == 0) {
                    Ssbhesabfa_Admin_Functions::log(array("This invoice is not define in OnlineStore. Order Number: " . $number));
                } else {
                    //check if order exist in prestashop
                    $id_obj = Ssbhesabfa_Admin_Functions::getObjectId('order', $id_order);
                    if ($id_obj > 0) {
                        global $wpdb;
                        $row = $wpdb->get_row("SELECT `id_hesabfa` FROM `".$wpdb->prefix."ssbhesabfa` WHERE `id` = $id_obj");
                        if (is_object($row) && $row->id_hesabfa != $number) {
                            $id_hesabfa_old = $row->id_hesabfa;
                            //ToDo: number must int, what can i do
                            $wpdb->update($wpdb->prefix . 'ssbhesabfa', array('id_hesabfa' => $number), array('id' => $id_obj));

                            Ssbhesabfa_Admin_Functions::log(array("Invoice Number changed. Old Number: $id_hesabfa_old. New ID: $number"));
                        }
                    }
                }
            }

            //2&3.check the change quantity and Price of Invoice items
            foreach ($invoice->Result->InvoiceItems as $invoiceItem) {
                $this->setItemChangesByCode($invoiceItem->Item->Code);
            }
        }
    }

    // use in webhook call when contact change
    public function setContactChangesById($id)
    {
        $hesabfaApi = new Ssbhesabfa_Api();
        $contact = $hesabfaApi->contactGetById(array($id));

        if ($contact->Success && !empty($contact->Result)) {
            //1.set new Hesabfa Contact Code if changes
            $code = $contact->Result[0]->Code;

            $json = json_decode($contact->Result[0]->Tag);
            if (is_object($json)) {
                $id_customer = $json->id_customer;
            } else {
                $id_customer = 0;
            }

            //check if Tag not set in hesabfa
            if ($id_customer == 0) {
                Ssbhesabfa_Admin_Functions::log(array("This Customer is not define in OnlineStore. Customer code: $code"));

                return false;
            }

            //check if customer exist in prestashop
            $id_obj = Ssbhesabfa_Admin_Functions::getObjectId('customer', $id_customer);
            if ($id_obj > 0) {
                global $wpdb;
                $row = $wpdb->get_row("SELECT `id_hesabfa` FROM `".$wpdb->prefix."ssbhesabfa` WHERE `id` = $id_obj");

                if (is_object($row) && $row->id_hesabfa != $code) {
                    $id_hesabfa_old = $row->id_hesabfa;
                    $wpdb->update($wpdb->prefix . 'ssbhesabfa', array('id_hesabfa' => (int)$code), array('id' => $id_obj));

                    Ssbhesabfa_Admin_Functions::log(array("Contact Code changed. Old ID: $id_hesabfa_old. New ID: $code"));
                }
            }
        }
    }

    public static function setItemChanges($item)
    {
        if (!is_object($item)) {
            return false;
        }

        $json = json_decode($item->Tag);
        if (is_object($json)) {
            $id_product = $json->id_product;
        } else {
            $id_product = 0;
        }

        //check if Tag not set in hesabfa
        if ($id_product == 0) {
            Ssbhesabfa_Admin_Functions::log(array("Item with code: $item->Code is not define in OnlineStore"));

            return false;
        }

        //check if product exist in prestashop
        $id_obj = Ssbhesabfa_Admin_Functions::getObjectId('product', $id_product);
        if ($id_obj > 0) {
            $product = new WC_Product($id_product);

            //1.set new Hesabfa Item Code if changes
            global $wpdb;
            $row = $wpdb->get_row("SELECT `id_hesabfa` FROM `".$wpdb->prefix."ssbhesabfa` WHERE `id` = $id_obj");

            if (is_object($row) && $row->id_hesabfa != $item->Code) {
                $id_hesabfa_old = $row->id_hesabfa;
                $wpdb->update($wpdb->prefix . 'ssbhesabfa', array('id_hesabfa' => (int)$item->Code), array('id' => $id_obj));

                Ssbhesabfa_Admin_Functions::log(array("Item Code changed. Old ID: $id_hesabfa_old. New ID: $item->Code"));

            }

            //2.set new Price
            if (get_option('ssbhesabfa_item_update_price')) {
                //ToDo check currency calculate
                $price = Ssbhesabfa::getPriceInHesabfaDefaultCurrency($product->price);
                if ($item->SellPrice != $price) {
                    $old_price = $product->get_price;
                    $product->set_price($item->SellPrice);

                    Ssbhesabfa_Admin_Functions::log(array("product ID $id_product Price changed. Old Price: $old_price. New Price: $item->SellPrice"));
                }
            }

            //3.set new Quantity
            if (get_option('ssbhesabfa_item_update_quantity')) {
                if ($item->Stock != $product->get_stock_quantity()) {
                    $old_quantity = $product->get_stock_quantity();
                    $product->set_stock_quantity($item->Stock);

                    Ssbhesabfa_Admin_Functions::log(array("product ID $id_product quantity changed. Old qty: $old_quantity. New qty: $item->Stock"));
                }
            }
        }
    }

    // use in webhook call when product change
    public function setItemChangesById($id)
    {
        $hesabfaApi = new Ssbhesabfa_Api();
        $item = $hesabfaApi->itemGetById(array($id));
        if ($item->Success && !empty($item->Result)) {
            $this->setItemChanges($item->Result[0]);
        }
    }

    // use in webhook call (in setInvoiceChangesById function) when invoice change
    public function setItemChangesByCode($code)
    {
        $hesabfaApi = new Ssbhesabfa_Api();
        $item = $hesabfaApi->itemGet($code);
        if ($item->Success && !empty($item->Result)) {
            $this->setItemChanges($item->Result);
        }
    }
}
