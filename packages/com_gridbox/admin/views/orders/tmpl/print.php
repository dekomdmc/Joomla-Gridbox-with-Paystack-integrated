<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

$date = JDate::getInstance($this->item->date)->format(gridboxHelper::$website->date_format);
$general = gridboxHelper::$store->general;
?>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function(){
    window.print();
});
</script>
<link rel="stylesheet" type="text/css" href="components/com_gridbox/assets/css/ba-admin.css">
<div style="width: 190mm;padding: 20mm;">
    <div style="width: 100%;padding-top: 5mm">
        <div style="width: 100%;text-align: left;">
            <span style="font-size: 2.5em;font-weight: bold;margin-top: 5mm; "><?php echo JText::_('INVOICE'); ?></span>
            <div style="margin-top: 3mm; ">
                <span style="flex-grow: 1;font-size:4mm;color: #00000080; ">
                    No. <?php echo $this->item->order_number; ?>
                </span>

                <span style="font-size:4mm;font-weight: normal;float: right;color: #00000080;"><?php echo $date; ?></span>
            </div>
        </div>

    </div>

    <div style="margin: 0 auto;padding: 0;width: 100%;">
        <div style="padding-top: 10mm;">
            <div style="width: 45%; display: inline-block;margin-bottom: 10mm;">
                <h3 style="font-size: 5mm;font-weight: bold;"><?php echo JText::_('FROM'); ?></h3>
<?php
                $business_name = !empty($general->business_name) ? $general->business_name.',' : '';
?>
                <p style="font-size: 4mm;color: #00000080;margin: 0;line-height: 36px;"><?php echo $business_name; ?></p>
<?php
$array = array();
if (!empty($general->country)) {
    $array[] = $general->country;
}
if (!empty($general->region)) {
    $array[] = $general->region;
}
$address = implode(', ', $array);
?>
                <p style="font-size: 4mm;color: #00000080;margin: 0;line-height: 36px;"><?php echo $address; ?></p>
<?php
$array = array();
if (!empty($general->city)) {
    $array[] = $general->city;
}
if (!empty($general->street)) {
    $array[] = $general->street;
}
if (!empty($general->zip_code)) {
    $array[] = $general->zip_code;
}
$address = implode(', ', $array);
?>
                <p style="font-size: 4mm;color: #00000080;margin: 0;line-height: 36px;"><?php echo $address; ?></p>
                <p style="font-size: 4mm;color: #00000080;margin: 0;line-height: 36px;"><?php echo $general->email; ?></p>
                <p style="font-size: 4mm;color: #00000080;margin: 0;line-height: 36px;"><?php echo $general->phone; ?></p>
            </div>
            <div  style="width: 45%; display: inline-block;float: right;margin-bottom: 10mm;">
                <h3 style="font-size: 5mm;font-weight: bold;"><?php echo JText::_('BILLED_TO'); ?></h3>
<?php
            foreach ($this->item->contact_info as $object) {
                if (!empty($object->title) && !empty($object->items)) {
?>
                <p style="font-size: 4mm;color: #000000;margin: 0;margin-top: 10mm;line-height: 36px;font-weight: bold;"><?php echo $object->title; ?></p>
<?php
                }
                foreach ($object->items as $info) {
                    $value = $info->value;
                    if ($info->type == 'country') {
                        $object = json_decode($value);
                        $values = array($object->region, $object->country);
                        $value = implode(', ', $values);
                    }
                    $value = str_replace('; ', ', ', $value);
?>
                <p style="font-size: 4mm;color: #00000080;margin: 0;line-height: 36px;"><?php echo $value; ?></p>
<?php
                }
            }
?>
            </div>
            <div  style="border-bottom: .2mm solid #f3f3f3;padding-bottom: 15px">
<?php
            $header = array(JText::_('NAME'), JText::_('DESCRIPTION'), JText::_('QTY'));
            $table = array(30, 30, 10, 15, 15);
            if (!empty($this->item->tax) && $this->item->tax_mode == 'incl') {
                $header[] = JText::_('NET_PRICE');
                $table = array(25, 20, 10, 15, 15, 15);
            }
            $header[] = JText::_('PRICE');
            $header[] = JText::_('AMOUNT');
?>
            <table style="width: 100%; ">
                <thead>
                    <tr style="background: #efefef !important;padding: 10px;-webkit-print-color-adjust: exact;">
<?php
                    foreach ($header as $i => $value) {
?>
                        <td style="padding: 10px; font-weight: bold;width: <?php echo $table[$i]; ?>%;"><?php echo $value; ?></td>
<?php
                    }
?>
                    </tr>
                </thead>
                <tbody>
<?php
$taxes = array();
foreach ($this->item->products as $product) {
    if (!empty($product->tax)) {
        $exist = false;
        foreach ($taxes as $tax) {
            if ($tax->title == $product->tax_title && $tax->rate == $product->tax_rate) {
                $tax->amount += $product->tax * 1;
                $exist = true;
                break;
            }
        }
        if (!$exist) {
            $tax = new stdClass();
            $tax->amount = $product->tax * 1;
            $tax->title = $product->tax_title;
            $tax->rate = $product->tax_rate;
            $taxes[] = $tax;
        }
    }
    $extraPrice = isset($product->extra_options->price) ? $product->extra_options->price : 0;
    $amount = ($product->sale_price !== '' ? $product->sale_price : $product->price) + $extraPrice * $product->quantity;
    $price = $amount / $product->quantity;
    $priceText = gridboxHelper::preparePrice($price, $this->item->currency_symbol, $this->item->currency_position);
    $info = array();
    foreach ($product->variations as $variation) {
        $info[] = '<span>'.$variation->title.' '.$variation->value.'</span>';
    }
    $infoStr = implode('<span>/</span>', $info);
    $amountText = gridboxHelper::preparePrice($amount, $this->item->currency_symbol, $this->item->currency_position);
    $data = array($product->title, $infoStr, $product->quantity);
    if (!empty($this->item->tax) && $this->item->tax_mode == 'incl') {
        $netPrice = $price - ($price - $price / ($product->tax_rate / 100 + 1));
        $netText = gridboxHelper::preparePrice($netPrice, $this->item->currency_symbol, $this->item->currency_position);
        $data[] = $netText;
    }
    $data[] = $priceText;
    $data[] = $amountText;
?>
                    <tr style="padding-top: 10px;">
<?php
                    foreach ($data as $value) {
?>
                        <td style="padding: 10px;color: #00000080;"><?php echo $value; ?></td>
<?php
                    }
?>
                    </tr>
<?php
            if (isset($product->extra_options->items)) {
                foreach ($product->extra_options->items as $extra) {
?>
                    <tr style="padding-top: 10px;">
<?php
                    foreach ($data as $i => $value) {
?>
                        <td style="padding: 20px 10px 5px;font-weight: bold; margin-top: 20px;"><?php echo $i == 1 ? $extra->title : ''; ?></td>
<?php
                    }
?>
                    </tr>
<?php
                    foreach ($extra->values as $value) {
                        $last = count($data) - 1;
?>
                    <tr style="padding-top: 5px;">
<?php
                    foreach ($data as $i => $v) {
                        if ($i == 1) {
                            $str = $value->value;
                        } else if ($i == $last && $value->price != '') {
                            $price = $value->price * $product->quantity;
                            $str = gridboxHelper::preparePrice($price, $this->item->currency_symbol, $this->item->currency_position);
                        } else {
                            $str = '';
                        }
?>
                        <td style="padding: 0 10px 0;color: #00000080;"><?php echo $str; ?></td>
<?php
                    }
?>
                    </tr>
<?php
                    }
                }
            }
}
$taxCount = count($taxes);
?>
                </tbody>
            </table>
    </div>
            <div style="margin-left: 60%;padding-top: 5mm;width: 40%;">
<?php
$price = gridboxHelper::preparePrice($this->item->subtotal, $this->item->currency_symbol, $this->item->currency_position);
?>
<div style="margin-top: 4mm;margin-bottom: 4mm; font-weight: bold;">
    <div style="display: inline-block;width: 65%;"><?php echo JText::_('SUBTOTAL'); ?>
    </div>
    <div style="width: 33%;display: inline-block;">
            <span><?php echo $price; ?></span>
    </div>
</div>
<?php
if ($this->item->promo) {
    $price = gridboxHelper::preparePrice($this->item->promo->value, $this->item->currency_symbol, $this->item->currency_position);
?>
<div style="margin-bottom: 4mm;">
    <div style="display: inline-block;width: 65%;color: #00000080;"><?php echo JText::_('DISCOUNT'); ?></div>
    <div style="width: 33%;display: inline-block;color: #00000080;">
        <span>-</span>
        <span><?php echo $price; ?></span>
    </div>
</div>
<?php
}
?>
<?php
if ($this->item->shipping) {
    $price = gridboxHelper::preparePrice($this->item->shipping->price, $this->item->currency_symbol, $this->item->currency_position);
    if ($this->item->shipping->type == 'free' || $this->item->shipping->type == 'pickup') {
        $price = JText::_('FREE');
    }
?>
<div style="margin-bottom: 4mm;">
    <div style="display: inline-block;width: 65%;color: #00000080;"><?php echo JText::_('SHIPPING'); ?></div>
    <div style="width: 33%;display: inline-block;color: #00000080;">
        <span><?php echo $price; ?></span>
    </div>
</div>
<?php
}
?>
<?php
if ($this->item->shipping && !empty($this->item->shipping->tax) && $this->item->tax_mode == 'incl') {
    $price = gridboxHelper::preparePrice($this->item->shipping->tax, $this->item->currency_symbol, $this->item->currency_position);
    $text = JText::_('INCLUDES').' '.$this->item->shipping->tax_title.' '.$price;
    $this->item->tax = $this->item->tax * 1 + $this->item->shipping->tax;
    if ($taxCount == 1) {
        foreach ($taxes as $tax) {
            if ($tax->title != $this->item->shipping->tax_title || $tax->rate != $this->item->shipping->tax_rate) {
                $taxCount++;
            }
        }
    }
?>
<div style="margin-bottom: 4mm;font-size: 10px;">
    <div style="display: inline-block;width: 100%;color: #00000080;"><?php echo $text; ?></div>
    
</div>
<?php
} else if ($this->item->shipping && !empty($this->item->shipping->tax)) {
    $price = gridboxHelper::preparePrice($this->item->shipping->tax, $this->item->currency_symbol, $this->item->currency_position);
?>
<div style="margin-bottom: 4mm;">
    <div style="display: inline-block;width: 65%;color: #00000080;"><?php echo JText::_('TAX_ON_SHIPPING'); ?></div>
    <div style="width: 33%;display: inline-block;color: #00000080;">
        <span><?php echo $price; ?></span>
    </div>
</div>
<?php
}
?>
<?php
if (!empty($this->item->tax) && $this->item->tax_mode == 'excl' && $taxCount == 0) {
    $price = gridboxHelper::preparePrice($this->item->tax, $this->item->currency_symbol, $this->item->currency_position);
?>
<div style="margin-bottom: 4mm;">
    <div style="display: inline-block;width: 65%;color: #00000080;"><?php echo JText::_('TAX'); ?></div>
    <div style="width: 33%;display: inline-block;color: #00000080;">
        <span><?php echo $price; ?></span>
    </div>
</div>
<?php
} else if (!empty($this->item->tax) && $this->item->tax_mode == 'excl') {
    foreach ($taxes as $tax) {
        $price = gridboxHelper::preparePrice($tax->amount, $this->item->currency_symbol, $this->item->currency_position);
?>
<div style="margin-bottom: 4mm;">
    <div style="display: inline-block;width: 65%;color: #00000080;"><?php echo $tax->title; ?></div>
    <div style="width: 33%;display: inline-block;color: #00000080;">
        <span><?php echo $price; ?></span>
    </div>
</div>
<?php
    }    
}
?>
<?php
$price = gridboxHelper::preparePrice($this->item->total, $this->item->currency_symbol, $this->item->currency_position);
?>
<div style="font-weight:bold;padding-top: 10mm;">
    <div style="display: inline-block;width: 65%;"><?php echo JText::_('TOTAL'); ?></div>
    <div style="width: 33%;display: inline-block;">
        <span><?php echo $price; ?></span>
    </div>
</div>
<?php
if (!empty($this->item->tax) && $this->item->tax_mode == 'incl') {
    $text = $taxCount == 1 ? JText::_('INCLUDES').' '.$taxes[0]->rate.'% '.$taxes[0]->title : JText::_('INCLUDING_TAXES');
    $price = gridboxHelper::preparePrice($this->item->tax, $this->item->currency_symbol, $this->item->currency_position);
?>
<div style="padding-top: 4mm;">
    <div style="display: inline-block;width: 100%;color: #00000080; font-size: 10px;"><?php echo $text.' '.$price; ?></div>
</div>
<?php
}
?>
            </div>
        </div>
    </div>
</div>
<?php
exit;