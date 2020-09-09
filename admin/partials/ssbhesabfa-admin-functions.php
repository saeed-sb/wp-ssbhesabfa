<?php

/**
 * @class      Ssbhesabfa_Admin_Functions
 * @version    1.0.8
 * @since      1.0.0
 * @package    ssbhesabfa
 * @subpackage ssbhesabfa/admin/functions
 * @author     Saeed Sattar Beglou <saeed.sb@gmail.com>
 */
class Ssbhesabfa_Admin_Functions
{
    public static function getObjectId($type, $id_ps, $id_ps_attribute = 0)
    {
        if (!isset($type) || !isset($id_ps)) {
            return false;
        }

        global $wpdb;
        $row = $wpdb->get_row("SELECT `id` FROM " . $wpdb->prefix . "ssbhesabfa WHERE `id_ps` = $id_ps AND `id_ps_attribute` = $id_ps_attribute AND `obj_type` = '$type'");

        if (is_object($row)) {
            return (int)$row->id;
        } else {
            return false;
        }
    }

    public static function getObjectIdByCode($type, $id_hesabfa)
    {
        if (!isset($type) || !isset($id_hesabfa)) {
            return false;
        }

        global $wpdb;
        $row = $wpdb->get_row("SELECT `id` FROM ".$wpdb->prefix . 'ssbhesabfa'." WHERE `id_hesabfa` = $id_hesabfa AND `obj_type` = '$type'");

        if (is_object($row))
            return $row->id;
        else
            return false;
    }

    public static function getItemCodeByProductId($id_product, $id_attribute = 0)
    {
        global $wpdb;
        $row = $wpdb->get_row("SELECT `id_hesabfa` FROM " . $wpdb->prefix . "ssbhesabfa WHERE `id_ps` = $id_product AND `id_ps_attribute` = $id_attribute AND `obj_type` = 'product'");

        if (is_object($row)) {
            return (int)$row->id_hesabfa;
        } else {
            return null;
        }
    }

    public static function isDateInFiscalYear($date) {
        $hesabfaApi = new Ssbhesabfa_Api();
        $fiscalYear = $hesabfaApi->settingGetFiscalYear();

        if (is_object($fiscalYear)) {
            if ($fiscalYear->Success) {
                $fiscalYearStartTimeStamp = strtotime($fiscalYear->Result->StartDate);
                $fiscalYearEndTimeStamp = strtotime($fiscalYear->Result->EndDate);
                $dateTimeStamp = strtotime($date);

                if ($dateTimeStamp >= $fiscalYearStartTimeStamp && $dateTimeStamp <= $fiscalYearEndTimeStamp) {
                    return 1;
                } else {
                    return 0;
                }
            } else {
                Ssbhesabfa_Admin_Functions::log(array("Cannot get FiscalDate. Error Code: $fiscalYear->ErrroCode. Error Message: $fiscalYear->ErrorMessage"));
                return false;
            }
        }

        Ssbhesabfa_Admin_Functions::log(array("Cannot connect to Hesabfa for get FiscalDate."));
        return false;
    }

    public function getProductVariations($id_product) {
        if (!isset($id_product)) {
            return false;
        }

        $product = wc_get_product($id_product);
        if ($product->is_type('variable')) {
            $children = $product->get_children($args = '', $output = OBJECT);
            $variations = array();
            foreach ($children as $value) {
                $product_variatons = new WC_Product_Variation($value);
                if ($product_variatons->exists() && $product_variatons->variation_is_visible()) {
                    $variations[] = $product_variatons;
                }
            }
            return $variations;
        }
        return false;
    }

    //Items
    public function setItem($id_product)
    {
        if (!isset($id_product)) {
            return false;
        }

        $code = $this->getItemCodeByProductId($id_product);
        if (!$code) {
            $code = null;
        }
        $product = new WC_Product($id_product);
        $categories = $product->get_category_ids();

        $item = array(
            'Code' => $code,
            'Name' => mb_substr($product->get_title(), 0, 99),
            'ItemType' => $product->is_virtual() == 1 ? 1 : 0,
            'Barcode' => $product->get_sku(),
            'SellPrice' => $this->getPriceInHesabfaDefaultCurrency($product->get_price()),
            'Tag' => json_encode(array('id_product' => $id_product, 'id_attribute' => 0)),
            'NodeFamily' => $this->getCategoryPath($categories[0]),
            'ProductCode' => $id_product,
        );
        $this->saveItem($item, $id_product);

        $variations = $this->getProductVariations($id_product);
        if ($variations != false) {
            foreach ($variations as $variation) {
                $id_attribute = $variation->get_id();
                $code = $this->getItemCodeByProductId($id_product, $id_attribute);
                if (!$code) {
                    $code = null;
                }
                $item = array(
                    'Code' => $code,
                    'Name' => mb_substr($variation->get_name(), 0, 99),
                    'ItemType' => $variation->is_virtual() == 1 ? 1 : 0,
                    'Barcode' => $variation->get_sku(),
                    'SellPrice' => $this->getPriceInHesabfaDefaultCurrency($variation->get_price()),
                    'Tag' => json_encode(array('id_product' => $id_product, 'id_attribute' => $id_attribute)),
                    'NodeFamily' => $this->getCategoryPath($categories[0]),
                    'ProductCode' => $id_product,
                );
                $this->saveItem($item, $id_product, $id_attribute);
            }
        }
    }

    public function saveItem($item, $id_product, $id_attribute = 0) {
        $hesabfa = new Ssbhesabfa_Api();
        $response = $hesabfa->itemSave($item);
        if ($response->Success) {
            global $wpdb;
            if ($item['Code'] == null) {
                $wpdb->insert($wpdb->prefix . 'ssbhesabfa', array(
                    'id_hesabfa' => (int)$response->Result->Code,
                    'obj_type' => 'product',
                    'id_ps' => $id_product,
                    'id_ps_attribute' => $id_attribute,
                ));

                Ssbhesabfa_Admin_Functions::log(array("Item successfully added. Item code: ".(string)$response->Result->Code.". Product ID: $id_product"));
            } else {
                $wpdb->update($wpdb->prefix . 'ssbhesabfa', array(
                    'id_hesabfa' => (int)$response->Result->Code,
                    'obj_type' => 'product',
                    'id_ps' => $id_product,
                    'id_ps_attribute' => $id_attribute,
                ), array('id' => $this->getObjectId('product', $id_product, $id_attribute)));

                Ssbhesabfa_Admin_Functions::log(array("Item successfully updated. Item code: ".(string)$response->Result->Code.". Product ID: $id_product"));
            }
            return $response->Result->Code;
        } else {
            Ssbhesabfa_Admin_Functions::log(array("Cannot add/update Hesabfa item. Error Code: ".(string)$response->ErrorCode.". Error Message: $response->ErrorMessage. Product ID: $id_product"));
            return false;
        }
    }

    public function getCategoryPath($id_category)
    {
        if (!isset($id_category))
            return;

        $path = get_term_parents_list($id_category, 'product_cat', array(
            'format' => 'name',
            'separator' => '/',
            'link' => false,
            'inclusive' => true,
        ));
        return $path;
    }

    //Contact
    public function getContactCodeByCustomerId($id_customer)
    {
        if (!isset($id_customer)) {
            return false;
        }

        global $wpdb;
        $row = $wpdb->get_row("SELECT `id_hesabfa` FROM " . $wpdb->prefix . "ssbhesabfa WHERE `id_ps` = $id_customer AND `obj_type` = 'customer'");

        if (is_object($row)) {
            return $row->id_hesabfa;
        } else {
            return false;
        }
    }

    public function setContact($id_customer)
    {
        if (!isset($id_customer)) {
            return false;
        }

        $code = $this->getObjectId('customer', $id_customer);
        if (!$code) {
            $code = null;
        }

        $customer = new WC_Customer($id_customer);
        $name = $customer->get_first_name() . ' ' . $customer->get_last_name();
        if (empty($customer->get_first_name()) && empty($customer->get_last_name())) {
            $name = __('Not Define', 'ssbhesabfa');
        }
        $data = array (
            array(
                'Code' => $code,
                'Name' => $name,
                'FirstName' => $customer->get_first_name(),
                'LastName' => $customer->get_last_name(),
                'ContactType' => 1,
                'NodeFamily' => 'اشخاص :' . get_option('ssbhesabfa_contact_node_family'),
                'Address' => $customer->get_billing_address(),
                'City' => $customer->get_billing_city(),
                'State' => $customer->get_billing_state(),
                'Country' => $customer->get_billing_country(),
                'PostalCode' => preg_replace("/[^0-9]/", '', $customer->get_billing_postcode()),
                'Phone' => preg_replace("/[^0-9]/", "", $customer->get_billing_phone()),
                'Email' => $this->validEmail($customer->get_email()) ? $customer->get_email() : null,
                'Tag' => json_encode(array('id_customer' => $id_customer)),
                'Note' => 'Customer ID in OnlineStore: ' . $id_customer,
            )
        );

        $hesabfa = new Ssbhesabfa_Api();
        $response = $hesabfa->contactBatchSave($data);

        if ($response->Success) {
            global $wpdb;
            if ($code == null) {
                $wpdb->insert($wpdb->prefix . 'ssbhesabfa', array(
                    'id_hesabfa' => (int)$response->Result[0]->Code,
                    'obj_type' => 'customer',
                    'id_ps' => $id_customer,
                ));

                Ssbhesabfa_Admin_Functions::log(array("Contact successfully added. Contact Code: ".(string)$response->Result[0]->Code.". Customer ID: $id_customer"));
            } else {
                $wpdb->update($wpdb->prefix . 'ssbhesabfa', array(
                    'id_hesabfa' => (int)$response->Result[0]->Code,
                    'obj_type' => 'customer',
                    'id_ps' => $id_customer,
                ), array('id' => $this->getObjectId('customer', $id_customer)));

                Ssbhesabfa_Admin_Functions::log(array("Contact successfully updated. Contact Code: ".(string)$response->Result[0]->Code.". Customer ID: $id_customer"));
            }
            return true;
        } else {
            Ssbhesabfa_Admin_Functions::log(array("Cannot add/update item. Error Code: ".(string)$response->ErrroCode.". Error Message: ".(string)$response->ErrorMessage.". Customer ID: $id_customer"));
            return false;
        }
    }

    public function setGuestCustomer($id_order)
    {
        if (!isset($id_order)) {
            return false;
        }

        $order = new WC_Order($id_order);

//        $code = $this->getContactCodeByEmail($order->get_billing_email());
//        if (!$code) {
            $code = null;
//        }

        $name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        if (empty($order->get_billing_first_name()) && empty($order->get_billing_last_name())) {
            $name = __('Guest Customer', 'ssbhesabfa');
        }
        $data = array (
            array(
                'Code' => $code,
                'Name' => $name,
                'FirstName' => $order->get_billing_first_name(),
                'LastName' => $order->get_billing_last_name(),
                'ContactType' => 1,
                'NodeFamily' => 'اشخاص :' . get_option('ssbhesabfa_contact_node_family'),
                'Address' => $order->get_billing_address_1() .' '.$order->get_billing_address_2(),
                'City' => $order->get_billing_city(),
                'State' => $order->get_billing_state(),
                'Country' => $order->get_billing_country(),
                'PostalCode' => preg_replace("/[^0-9]/", '', $order->get_billing_postcode()),
                'Phone' => preg_replace("/[^0-9]/", "", $order->get_billing_phone()),
                'Email' => $this->validEmail($order->get_billing_email()) ? $order->get_billing_email() : null,
                'Tag' => json_encode(array('id_customer' => 0)),
                'Note' => __('Customer registered as a GuestCustomer.', 'ssbhesabfa'),
            )
        );

        $hesabfa = new Ssbhesabfa_Api();
        $response = $hesabfa->contactBatchSave($data);

        if ($response->Success) {
            global $wpdb;
            if ($code == null) {
                $id_customer = 0;
                $wpdb->insert($wpdb->prefix . 'ssbhesabfa', array(
                    'id_hesabfa' => (int)$response->Result[0]->Code,
                    'obj_type' => 'customer',
                    'id_ps' => $id_customer,
                ));

                Ssbhesabfa_Admin_Functions::log(array("Contact successfully added. Contact Code: ".(string)$response->Result[0]->Code.". Customer ID: GuestCustomer"));
            } //else {
//                $wpdb->update($wpdb->prefix . 'ssbhesabfa', array(
//                    'id_hesabfa' => (int)$response->Result[0]->Code,
//                    'obj_type' => 'customer',
//                    'id_ps' => $id_customer,
//                ), array('id' => $this->getObjectId('customer', $id_customer)));
//
//                Ssbhesabfa_Admin_Functions::log(array("Contact successfully updated. Contact Code: ".(string)$response->Result[0]->Code.". Customer ID: $id_customer"));
//            }
            return (int)$response->Result[0]->Code;
        } else {
            Ssbhesabfa_Admin_Functions::log(array("Cannot add/update item. Error Code: ".(string)$response->ErrroCode.". Error Message: ".(string)$response->ErrorMessage.". Customer ID: $id_customer"));
            return false;
        }
    }

    public function setContactAddress($id_customer, $type = 'billing')
    {
        if (!isset($id_customer)) {
            return false;
        }
        $customer = new WC_Customer($id_customer);

        $code = $this->getObjectId('customer', $id_customer);
        if (!$code) {
            $code = null;
        }

        $name = $customer->get_first_name() . ' ' . $customer->get_last_name();
        if (empty($customer->get_first_name()) && empty($customer->get_last_name())) {
            $name = __('Guest Customer', 'ssbhesabfa');
        }

        if ($type === 'first') {
            $data = array (
                array(
                    'Code' => $code,
                    'Name' => $name,
                    'FirstName' => $customer->get_first_name(),
                    'LastName' => $customer->get_last_name(),
                    'ContactType' => 1,
                    'Tag' => json_encode(array('id_customer' => $id_customer)),
                )
            );
        } elseif ($type === 'billing') {
            $data = array (
                array(
                    'Code' => $code,
                    'Name' => $name,
                    'FirstName' => $customer->get_first_name(),
                    'LastName' => $customer->get_last_name(),
                    'ContactType' => 1,
                    'Address' => $customer->get_billing_address(),
                    'City' => $customer->get_billing_city(),
                    'State' => $customer->get_billing_state(),
                    'Country' => $customer->get_billing_country(),
                    'PostalCode' => preg_replace("/[^0-9]/", '', $customer->get_billing_postcode()),
                    'Phone' => preg_replace("/[^0-9]/", "", $customer->get_billing_phone()),
                    'Email' => $this->validEmail($customer->get_email()) ? $customer->get_email() : null,
                    'Tag' => json_encode(array('id_customer' => $id_customer)),

                )
            );
        } elseif ($type === 'shipping') {
            $data = array (
                array(
                    'Code' => $code,
                    'Name' => $name,
                    'FirstName' => $customer->get_first_name(),
                    'LastName' => $customer->get_last_name(),
                    'ContactType' => 1,
                    'Address' => $customer->get_shipping_address(),
                    'City' => $customer->get_shipping_city(),
                    'State' => $customer->get_shipping_state(),
                    'Country' => $customer->get_shipping_country(),
                    'PostalCode' => preg_replace("/[^0-9]/", '', $customer->get_shipping_postcode()),
                    'Phone' => preg_replace("/[^0-9]/", "", $customer->get_shipping_phone()),
                    'Email' => $this->validEmail($customer->get_email()) ? $customer->get_email() : null,
                    'Tag' => json_encode(array('id_customer' => $id_customer)),
                )
            );
        }

        $hesabfa = new Ssbhesabfa_Api();
        $response = $hesabfa->contactBatchSave($data);

        if ($response->Success) {
            Ssbhesabfa_Admin_Functions::log(array("Contact address successfully updated. Contact Code: ".(string)$response->Result[0]->Code.". Customer ID: $id_customer"));
            return true;
        } else {
            Ssbhesabfa_Admin_Functions::log(array("Cannot add/update contact address. Error Code: ".(string)$response->ErrorCode.". Error Message: ".(string)$response->ErrorMessage.". Customer ID: $id_customer"));
            return false;
        }
    }

    public function validEmail($email)
    {
        $isValid = true;
        $atIndex = strrpos($email, "@");
        if (is_bool($atIndex) && !$atIndex)
        {
            $isValid = false;
        }
        else
        {
            $domain = substr($email, $atIndex+1);
            $local = substr($email, 0, $atIndex);
            $localLen = strlen($local);
            $domainLen = strlen($domain);
            if ($localLen < 1 || $localLen > 64)
            {
                // local part length exceeded
                $isValid = false;
            }
            else if ($domainLen < 1 || $domainLen > 255)
            {
                // domain part length exceeded
                $isValid = false;
            }
            else if ($local[0] == '.' || $local[$localLen-1] == '.')
            {
                // local part starts or ends with '.'
                $isValid = false;
            }
            else if (preg_match('/\\.\\./', $local))
            {
                // local part has two consecutive dots
                $isValid = false;
            }
            else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
            {
                // character not valid in domain part
                $isValid = false;
            }
            else if (preg_match('/\\.\\./', $domain))
            {
                // domain part has two consecutive dots
                $isValid = false;
            }
            else if
            (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                str_replace("\\\\","",$local)))
            {
                // character not valid in local part unless
                // local part is quoted
                if (!preg_match('/^"(\\\\"|[^"])+"$/',
                    str_replace("\\\\","",$local)))
                {
                    $isValid = false;
                }
            }
        }
        return $isValid;
    }

    public function getContactCodeByEmail($email) {
        $queryInfo = array(
            'SortBy' => 'Code',
            'SortDesc' => true,
            'Take' => 1,
            'Skip' => 0,
            'Filters' => array(array(
                'Property' => 'Email',
                'Operator' => '=',
                'Value' => $email,
            ))
        );

        $hesabfa = new Ssbhesabfa_Api();
        $response = $hesabfa->contactGetContacts($queryInfo);

        if (is_object($response)) {
            if ($response->Success && $response->Result->TotalCount > 0) {
                $contact_obj = $response->Result->List;

                return (int)$contact_obj[0]->Code;
            }
        } else {
            Ssbhesabfa_Admin_Functions::log(array("Cannot get Contact list. Error Message: (string)$response->ErrorMessage. Error Code: (string)$response->ErrorCode."));
        }

        return false;
    }

    //Invoice
    public function setOrder($id_order, $orderType = 0)
    {
        if (!isset($id_order)) {
            return false;
        }

        $order = new WC_Order($id_order);

        $id_customer = $order->get_customer_id();

        if ($id_customer !== 0) {
            $contactCode = $this->getObjectId('customer', $id_customer);

            if (!$contactCode) {
                // set customer if not exists
                $this->setContact($id_customer);
            } elseif (get_option('ssbhesabfa_contact_address_status') == 1) {
                // update customer name only
                $this->setContactAddress($id_customer, 'first');
            } elseif (get_option('ssbhesabfa_contact_address_status') == 2) {
                $this->setContactAddress($id_customer, 'billing');
            } elseif (get_option('ssbhesabfa_contact_address_status') == 3) {
                $this->setContactAddress($id_customer, 'shipping');
            }
        } else {
            // set guest customer
            $contactCode = $this->setGuestCustomer($id_order);
            if (!$contactCode) {
                // return false if cannot set guest customer
                return false;
            }
        }

        $items = array();
        $i = 0;

        $products = $order->get_items();
        foreach ($products as $key => $product) {
            $itemCode = $this->getItemCodeByProductId($product['product_id'], $product['variation_id']);

            // add product before insert invoice
            if ($itemCode == null) {
                $itemCode = $this->setItem($product['product_id']);
            }

            $item = array (
                'RowNumber' => $i,
                'ItemCode' => $itemCode,
                'Description' => $product['name'],
                'Quantity' => (int)$product['quantity'],
                'UnitPrice' => (float)$this->getPriceInHesabfaDefaultCurrency($product['subtotal'] / $product['quantity']),
                'Discount' => (float)$this->getPriceInHesabfaDefaultCurrency($product['subtotal'] - $product['total']),
                'Tax' => (float)$this->getPriceInHesabfaDefaultCurrency($product['subtotal_tax']),
            );
            array_push($items, $item);
            $i++;
        }

        $number = $this->getObjectId('order', $id_order);
        if (!$number) {
            $number = null;
        }

        $date_obj = $order->get_date_created();
        switch ($orderType) {
            case 0:
                $date = $date_obj->date('Y-m-d H:i:s');
                break;
            case 2:
                $date = date('Y-m-d H:i:s');
                break;
            default:
                $date = $date_obj->date('Y-m-d H:i:s');
        }

        $data = array (
            'Number' => $number,
            'InvoiceType' => $orderType,
            'ContactCode' => $contactCode,
            'Date' => $date,
            'DueDate' => $date,
            'Reference' => $id_order,
            'Status' => 2,
            'Tag' => json_encode(array('id_order' => $id_order)),
            'Freight' => $this->getPriceInHesabfaDefaultCurrency($order->get_shipping_total()),
            'InvoiceItems' => $items,
        );

        $hesabfa = new Ssbhesabfa_Api();
        $response = $hesabfa->invoiceSave($data);

        if ($response->Success) {
            global $wpdb;
            switch ($orderType) {
                case 0:
                    $obj_type = 'order';
                    break;
                case 2:
                    $obj_type = 'returnOrder';
                    break;
            }

            if ($number == null) {
                $wpdb->insert($wpdb->prefix . 'ssbhesabfa', array(
                    'id_hesabfa' => (int)$response->Result->Number,
                    'obj_type' => $obj_type,
                    'id_ps' => $id_order,
                ));
                Ssbhesabfa_Admin_Functions::log(array("Invoice successfully added. Invoice number: ".(string)$response->Result->Number.". Order ID: $id_order"));
            } else {
                $wpdb->update($wpdb->prefix . 'ssbhesabfa', array(
                    'id_hesabfa' => (int)$response->Result->Number,
                    'obj_type' => $obj_type,
                    'id_ps' => $id_order,
                ), array('id' => $this->getObjectId('order', $id_order)));
                Ssbhesabfa_Admin_Functions::log(array("Invoice successfully updated. Invoice number: ".(string)$response->Result->Number.". Order ID: $id_order"));
            }

            return true;
        } else {
            Ssbhesabfa_Admin_Functions::log(array("Cannot add/update Invoice. Error Code: ".(string)$response->ErrorCode.". Error Message: ".(string)$response->ErrorMessage.". Order ID: $id_order"));
            return false;
        }
    }

    public static function getPriceInHesabfaDefaultCurrency($price)
    {
        if (!isset($price)) {
            return false;
        }

        $woocommerce_currency = get_woocommerce_currency();
        $hesabfa_currency = get_option('ssbhesabfa_hesabfa_default_currency');

        if ($hesabfa_currency == 'IRR' && $woocommerce_currency == 'IRT') {
            $price *= 10;
        }

        return $price;
    }

//    public function convert_currency( $price ) {
//
//        switch ( get_woocommerce_currency() ) {
//            case 'IRT':
//                $price /= 10;
//                break;
//            case 'IRHR':
//                $price /= 1000;
//                break;
//            case 'IRHT':
//                $price /= 10000;
//                break;
//        }
//
//        return ceil( $price );
//    }

    public function setOrderPayment($id_order)
    {
        if (!isset($id_order)) {
            return false;
        }

        $hesabfa = new Ssbhesabfa_Api();
        $number = $this->getObjectId('order', (int)$id_order);
        if (!$number)
            $number = null;

        $order = new WC_Order($id_order);

        //Skip free order payment
        if ($order->get_total() <= 0) {
            return true;
        }

        $bank_code = $this->getBankCodeByPaymentMethod($order->get_payment_method());
        if ($bank_code == -1) {
            return true;
        } elseif ($bank_code != false) {
            $transaction_id = $order->get_transaction_id();
            //fix Hesabfa API error
            if ($transaction_id == '') {
                $transaction_id = 'None';
            }

            $date_obj = $order->get_date_paid();

            $response = $hesabfa->invoiceSavePayment($number, $bank_code, $date_obj->date('Y-m-d H:i:s'), $this->getPriceInHesabfaDefaultCurrency($order->get_total()), $transaction_id, $order->get_customer_ip_address());

            if ($response->Success) {
                Ssbhesabfa_Admin_Functions::log(array("Hesabfa invoice payment added. Order ID: $id_order"));
            } else {
                Ssbhesabfa_Admin_Functions::log(array("Cannot add Hesabfa Invoice payment. Order ID: $id_order. Error Code: ".(string)$response->ErrorCode.". Error Message: ".(string)$response->ErrorMessage."."));
            }
        } else {
            Ssbhesabfa_Admin_Functions::log(array("Cannot add Hesabfa Invoice payment - Bank Code not define. Order ID: $id_order"));
        }
    }

    public function getBankCodeByPaymentMethod($payment_method)
    {
        $code = get_option('ssbhesabfa_payment_method_' . $payment_method);

        if (isset($code))
            return $code;
        else
            return false;
    }

    //Export
    public function exportProducts()
    {
        $args = array('post_type' => 'product', 'posts_per_page' => -1);
        $products = get_posts($args);

        $items = array();

        foreach ($products as $item) {
            $id_product = $item->ID;

            //do if product not exists in hesabfa
            $id_obj = $this->getObjectId('product', $id_product);
            if (!$id_obj) {
                $product = new WC_Product($id_product);
                $categories = $product->get_category_ids();
                $name = mb_substr($product->get_title(),0,99);

                array_push($items, array(
                    'Name' => $name,
                    'ItemType' => $product->is_virtual() == 1 ? 1 : 0,
                    'Barcode' => $product->get_sku(),
                    'SellPrice' => $this->getPriceInHesabfaDefaultCurrency($product->get_price()),
                    'Tag' => json_encode(array('id_product' => $id_product, 'id_attribute' => 0)),
                    'NodeFamily' => $this->getCategoryPath($categories[0]),
                    'ProductCode' => $id_product,
                ));
            }

            $variations = $this->getProductVariations($id_product);
            if ($variations != false) {
                foreach ($variations as $variation) {
                    $id_attribute = $variation->get_id();
                    $id_obj = $this->getObjectId('product', $id_product, $id_attribute);
                    if (!$id_obj) {
                        array_push($items, array(
                            'Name' => mb_substr($variation->get_name(), 0, 99),
                            'ItemType' => $variation->is_virtual() == 1 ? 1 : 0,
                            'Barcode' => $variation->get_sku(),
                            'SellPrice' => $this->getPriceInHesabfaDefaultCurrency($variation->get_price()),
                            'Tag' => json_encode(array('id_product' => $id_product, 'id_attribute' => $id_attribute)),
                            'NodeFamily' => $this->getCategoryPath($categories[0]),
                            'ProductCode' => $id_product,
                        ));
                    }
                }
            }
        }

        if (!empty($items)) {
            $hesabfa = new Ssbhesabfa_Api();
            $response = $hesabfa->itemBatchSave($items);
            if ($response->Success) {
                foreach ($response->Result as $item) {
                    $json = json_decode($item->Tag);

                    global $wpdb;
                    $wpdb->insert($wpdb->prefix . 'ssbhesabfa', array(
                        'id_hesabfa' => (int)$item->Code,
                        'obj_type' => 'product',
                        'id_ps' => (int)$json->id_product,
                        'id_ps_attribute' => (int)$json->id_attribute,
                    ));
                    Ssbhesabfa_Admin_Functions::log(array("Item successfully added. Item Code: ".(string)$item->Code.". Product ID: $id_product"));
                }
                return count($response->Result);
            } else {
                Ssbhesabfa_Admin_Functions::log(array("Cannot add bulk item. Error Message: ".(string)$response->ErrorMessage.". Error Code: ".(string)$response->ErrorCode."."));
            }
        }
        return false;
    }

    public function exportOpeningQuantity() {
        $args = array('post_type' => 'product', 'posts_per_page' => -1);
        $products = get_posts($args);

        $items = array();

        foreach ($products as $item) {
            $variations = $this->getProductVariations($item->ID);
            if (!$variations) {
                //do if product exists in hesabfa
                $id_obj = $this->getObjectId('product', $item->ID);
                if ($id_obj != false) {
                    $product = new WC_Product($item->ID);
                    $quantity = $product->get_stock_quantity();
                    $price = $product->get_price();

                    global $wpdb;
                    $row = $wpdb->get_row("SELECT `id_hesabfa` FROM `".$wpdb->prefix."ssbhesabfa` WHERE `id` = ". $id_obj ." AND `obj_type` = 'product'");

                    if (is_object($product) && is_object($row) && $quantity > 0 && $price > 0) {
                        array_push($items, array(
                            'Code' => $row->id_hesabfa,
                            'Quantity' => $quantity,
                            'UnitPrice' => $this->getPriceInHesabfaDefaultCurrency($price),
                        ));
                    }
                }
            } else {
                foreach ($variations as $variation) {
                    //do if product exists in hesabfa
                    $id_attribute = $variation->get_id();
                    $id_obj = $this->getObjectId('product', $item->ID, $id_attribute);
                    if ($id_obj != false) {
                        $quantity = $variation->get_stock_quantity();
                        $price = $variation->get_price();

                        global $wpdb;
                        $row = $wpdb->get_row("SELECT `id_hesabfa` FROM `".$wpdb->prefix."ssbhesabfa` WHERE `id` = ". $id_obj ." AND `obj_type` = 'product'");

                        if (is_object($variation) && is_object($row) && $quantity > 0 && $price > 0) {
                            array_push($items, array(
                                'Code' => $row->id_hesabfa,
                                'Quantity' => $quantity,
                                'UnitPrice' => $this->getPriceInHesabfaDefaultCurrency($price),
                            ));
                        }
                    }
                }
            }
        }

        //call API when at least one product exists
        if (!empty($items)) {
            $hesabfa = new Ssbhesabfa_Api();
            $response = $hesabfa->itemUpdateOpeningQuantity($items);
            if ($response->Success) {
                Ssbhesabfa_Admin_Functions::log(array('ssbhesabfa - Opening quantity successfully added.'));
                return true;
            } else {
                Ssbhesabfa_Admin_Functions::log(array('ssbhesabfa - Cannot set Opening quantity. Error Code: ' . $response->ErrorCode . '. Error Message: ' . $response->ErrorMessage));
                return false;
            }
        } else {
            Ssbhesabfa_Admin_Functions::log(array('ssbhesabfa - No product available for set Opening quantity.'));
            return true;
        }
    }

    public function exportCustomers()
    {
        $customers = get_users( 'orderby=id&role=subscriber' );
        $data = array();
        foreach ($customers as $item) {
            //do if customer not exists in hesabfa
            $id_customer = $item->ID;
            $id_obj = $this->getObjectId('customer', $id_customer);
            if (!$id_obj) {
                $customer = new WC_Customer($id_customer);

                $name = $customer->get_first_name() . ' ' . $customer->get_last_name();
                if (empty($customer->get_first_name()) && empty($customer->get_last_name())) {
                    $name = __('Not Define', 'ssbhesabfa');
                }

                array_push($data, array(
                    'Name' => $name,
                    'FirstName' => $customer->get_first_name(),
                    'LastName' => $customer->get_last_name(),
                    'ContactType' => 1,
                    'NodeFamily' => 'اشخاص :' . get_option('ssbhesabfa_contact_node_family'),
                    'Address' => $customer->get_billing_address(),
                    'City' => $customer->get_billing_city(),
                    'State' => $customer->get_billing_state(),
                    'Country' => $customer->get_billing_country(),
                    'PostalCode' => preg_replace("/[^0-9]/", '', $customer->get_billing_postcode()),
                    'Phone' => preg_replace("/[^0-9]/", "", $customer->get_billing_phone()),
                    'Email' => $this->validEmail($customer->get_email()) ? $customer->get_email() : null,
                    'Tag' => json_encode(array('id_customer' => $id_customer)),
                    'Note' => 'Customer ID in OnlineStore: ' . $id_customer,                ));
            }
        }

        if (!empty($data)) {
            $hesabfa = new Ssbhesabfa_Api();
            $response = $hesabfa->contactBatchSave($data);

            if ($response->Success) {
                foreach ($response->Result as $item) {
                    $json = json_decode($item->Tag);

                    global $wpdb;
                    $wpdb->insert($wpdb->prefix . 'ssbhesabfa', array(
                        'id_hesabfa' => (int)$item->Code,
                        'obj_type' => 'customer',
                        'id_ps' => (int)$json->id_customer,
                    ));

                    Ssbhesabfa_Admin_Functions::log(array("Contact successfully added. Contact Code: ".$item->Code.". Customer ID: " . (int)$json->id_customer));
                }
                return count($response->Result);
            } else {
                Ssbhesabfa_Admin_Functions::log(array("Cannot add bulk contacts. Error Message: $response->ErrorMessage. Error Code: $response->ErrorCode."));
            }
        }

        return false;
    }

    public function syncOrders($from_date)
    {
        if (!isset($from_date)) {
            return false;
        }

        if (!$this->isDateInFiscalYear($from_date)) {
            return 'fiscalYearError';
        }

        $orders = wc_get_orders(array(
            'date_created' => '>' . $from_date,
        ));

        $id_orders = array();
        foreach ($orders as $order) {
            $id_order = $order->get_id();
            $id_obj = $this->getObjectId('order', $id_order);
            if (!$id_obj) {
                if ($this->setOrder($id_order)) {
                    $this->setOrderPayment($id_order);
                    array_push($id_orders, $id_order);
                }
            }

            $order = wc_get_order($id_order);
            if ($order) {
                $current_status = $order->get_status();
                foreach (get_option('ssbhesabfa_invoice_return_status') as $status) {
                    if ($status == $current_status) {
                        $this->setOrder($id_order, 2);
                    }
                }
            }
        }

        return $id_orders;
    }

    public function syncProducts()
    {
        $hesabfa = new Ssbhesabfa_Api();
        $response = $hesabfa->itemGetItems(array('Take' => 99999999));
        if ($response->Success) {
            $products = $response->Result->List;
            require_once plugin_dir_path(dirname(__FILE__)) . '../includes/class-ssbhesabfa-webhook.php';

            $webhook = new Ssbhesabfa_Webhook();
            foreach ($products as $product) {
                $webhook->setItemChanges($product);
            }
            return true;
        }

        Ssbhesabfa_Admin_Functions::log(array("Cannot get bulk item. Error Message: (string)$response->ErrorMessage. Error Code: (string)$response->ErrorCode."));
        return false;
    }

    public static function log($params) {
        $log = '';

        foreach ( $params as $message ) {
            if ( is_array( $message ) || is_object( $message ) ) {
                $log .= date( '[r] ' ) . print_r( $message, true ) . "\n";
            } elseif ( is_bool( $message ) ) {
                $log .= date( '[r] ' ) . ( $message ? 'true' : 'false' ) . "\n";
            } else {
                $log .= date( '[r] ' ) . $message . "\n";
            }
        }

        file_put_contents( WP_CONTENT_DIR . '/ssbhesabfa.log', $log, FILE_APPEND );
    }
}