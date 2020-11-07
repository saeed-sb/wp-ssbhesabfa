<?php

/*
 * @class      Ssbhesabfa_Validation
 * @version    1.1.5
 * @since      1.1.5
 * @package    ssbhesabfa
 * @subpackage ssbhesabfa/includes
 * @author     Saeed Sattar Beglou <saeed.sb@gmail.com>
 */

class Ssbhesabfa_Validation
{
    //item
    public static function itemCodeValidation($code) {
        $code = preg_replace('/[^0-9]/', '', $code);
        return mb_substr($code, 0, 5);
    }

    public static function itemNameValidation($name) {
        return mb_substr($name, 0, 99);
    }

    public static function itemBarcodeValidation($barcode) {
        return mb_substr($barcode, 0, 999);
    }

    public static function itemCategoryValidation($category) {
        return $category;
    }

    public static function itemDescriptionValidation($description) {
        return mb_substr($description, 0, 199);
    }

    public static function itemMainUnitValidation($mainUnit) {
        return mb_substr($mainUnit, 0, 29);
    }

    public static function itemSubUnitValidation($subUnit) {
        return mb_substr($subUnit, 0, 29);
    }

    public static function itemConversionFactorValidation($conversionFactor) {
        if ($conversionFactor < 0) {
            return 0;
        } else {
            return $conversionFactor;
        }
    }

    public static function itemSalesTaxValidation($salesTax) {
        if ($salesTax >= 0 && $salesTax <= 100) {
            return $salesTax;
        } else {
            return 0;
        }
    }

    public static function itemSalesInfoValidation($salesInfo) {
        return mb_substr($salesInfo, 0, 99);
    }

    public static function itemPurchaseCostValidation($purchaseCost) {
        if ($purchaseCost >= 0) {
            return $purchaseCost;
        } else {
            return 0;
        }
    }

    public static function itemPurchaseInfoValidation($purchaseInfo) {
        return mb_substr($purchaseInfo, 0, 99);
    }

    public static function itemTagValidation($tag) {
        return mb_substr($tag, 0, 254);
    }

    //Contact
    public static function contactCodeValidation($code) {
        $code = preg_replace('/[^0-9]/', '', $code);
        return mb_substr($code, 0, 5);
    }

    public static function contactDisplayNameValidation($displayName) {
        return mb_substr($displayName, 0, 99);
    }

    public static function contactCompanyValidation($company) {
        return mb_substr($company, 0, 99);
    }

    public static function contactTitleValidation($title) {
        return mb_substr($title, 0, 49);
    }

    public static function contactFirstNameValidation($firstName) {
        return mb_substr($firstName, 0, 49);
    }

    public static function contactLastNameValidation($lastName) {
        return mb_substr($lastName, 0, 49);
    }

    public static function contactNationalCodeValidation($nationalCode) {
        return mb_substr($nationalCode, 0, 10);
    }

    public static function contactEconomicCodeValidation($economicCode) {
        return mb_substr($economicCode, 0, 11);
    }

    public static function contactRegistrationNumberValidation($registrationNumber) {
        return mb_substr($registrationNumber, 0, 11);
    }

    public static function contactAddressValidation($address) {
        return mb_substr($address, 0, 149);
    }

    public static function contactCountryValidation($country) {
        return mb_substr($country, 0, 49);
    }

    public static function contactStateValidation($state) {
        return mb_substr($state, 0, 49);
    }

    public static function contactCityValidation($city) {
        return mb_substr($city, 0, 49);
    }

    public static function contactPostalCodeValidation($postalCode) {
        $postalCode = preg_replace('/[^0-9]/', '', $postalCode);
        return mb_substr($postalCode, 0, 9);
    }

    public static function contactPhoneValidation($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return mb_substr($phone, 0, 14);
    }

    public static function contactMobileValidation($mobile) {
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        return mb_substr($mobile, 0, 14);
    }

    public static function contactFaxValidation($fax) {
        $fax = preg_replace('/[^0-9]/', '', $fax);
        return mb_substr($fax, 0, 14);
    }

    public static function contactEmailValidation($email) {
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

        if ($isValid) {
            return $email;
        } else {
            return null;
        }
    }

    public static function contactWebsiteValidation($website) {
        return mb_substr($website, 0, 119);
    }

    public static function contactNoteValidation($note) {
        return mb_substr($note, 0, 499);
    }

    public static function contactCategoryValidation($category) {
        return $category;
    }

    public static function contactTagValidation($tag) {
        return mb_substr($tag, 0, 254);
    }

    //Invoice

    public static function invoiceFinancialYearValidation($financialYear) {
        return $financialYear;
    }

    public static function invoiceCurrencyRateValidation($currencyRate) {
        if ($currencyRate > 0) {
            return $currencyRate;
        } else {
            return 1;
        }
    }

    public static function invoiceNumberValidation($number) {
        return mb_substr($number, 0, 49);
    }

    public static function invoiceContactTitleValidation($contactTitle) {
        return mb_substr($contactTitle, 0, 199);
    }

    public static function invoiceDueDateValidation($dueDate) {
        return $dueDate;
    }

    public static function invoiceNoteValidation($note) {
        return mb_substr($note, 0, 499);
    }

    public static function invoiceReferenceValidation($reference) {
        return mb_substr($reference, 0, 49);
    }

    public static function invoiceTagValidation($tag) {
        return mb_substr($tag, 0, 254);
    }

    public function invoiceItemsValidation($items) {
        return $items;
    }

    //Invoice items
    public static function invoiceItemDescriptionValidation($description) {
        return mb_substr($description, 0, 249);
    }

    public static function invoiceItemQuantityValidation($quantity) {
        if ($quantity > 0) {
            return $quantity;
        } else {
            return 1;
        }
    }

    public static function invoiceItemUnitValidation($unit) {
        return mb_substr($unit, 0, 29);
    }

    public static function invoiceItemUnitPriceValidation($unitPrice) {
        if ($unitPrice >= 0) {
            return $unitPrice;
        } else {
            return 0;
        }
    }

    public static function invoiceItemAmountValidation($amount) {
        if ($amount >= 0) {
            return $amount;
        } else {
            return 0;
        }
    }

    public static function invoiceItemDiscountValidation($discount) {
        if ($discount >= 0) {
            return $discount;
        } else {
            return 0;
        }
    }

    public static function invoiceItemTaxValidation($tax) {
        if ($tax >= 0) {
            return $tax;
        } else {
            return 0;
        }
    }

    public static function invoiceItemTotalAmountValidation($totalAmount) {
        if ($totalAmount >= 0) {
            return $totalAmount;
        } else {
            return 0;
        }
    }
}
