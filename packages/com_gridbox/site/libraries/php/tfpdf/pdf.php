<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;
include 'tfpdf.php';

class pdf extends tFPDF
{
	public $store;

	public function preparePrice($price, $symbol, $position)
    {
        $decimals = $this->store->currency->decimals;
        $separator = $this->store->currency->separator;
        $thousand = $this->store->currency->thousand;
        $price = round($price * 1, $decimals);
        $price = number_format($price, $decimals, $separator, $thousand);
        if ($position == '') {
            $value = $symbol.' '.$price;
        } else {
            $value = $price.' '.$symbol;
        }

        return $value;
    }

    public function setCoordinates($l, $x, $y)
    {
        $this->SetLeftMargin($l);
        $this->setX($x);
        $this->setY($y);
    }

    public function setTextCell($l, $w, $h, $y, $text, $a, $f = false)
    {
        $this->setCoordinates($l, $l, $y);
        $this->MultiCell($w, $h, $text, 0, $a, $f);
        $y = $this->getY();

        return $y;
    }

    public function setCartTotal($l, $w, $r, $w1, $y, $title, $value, $item, $promo = false)
    {
        if ($value == JText::_('FREE')) {
            $price = $value;
        } else {
            $price = $this->preparePrice($value, $item->currency_symbol, $item->currency_position);
        }
        if ($promo) {
            $price = '-'.$price;
        }
        $y0 = $this->setTextCell($l, $w, 8, $y, $title, 'L');
        $y1 = $this->setTextCell($r, $w1, 8, $y, $price, 'L');
        $y = max($y0, $y1);

        return $y;
    }

    public function create($item, $general, $dest = 'D', $path = '')
    {
    	if (!empty($path) && !is_dir($path)) {
    		return '';
    	}
        $dir = '/administrator/components/com_gridbox/assets/fonts/';
        $this->SetAutoPageBreak(true);
        $this->AddPage();
        $this->AddFont('Roboto', 'Regular', $dir.'Roboto-Regular.ttf', 'Roboto-Regular', true);
        $this->AddFont('Roboto', 'Bold', $dir.'Roboto-Bold.ttf', 'Roboto-Bold', true);
        $this->SetDrawColor(243, 243, 243);
        $margin = 20;
        $pw = $this->GetPageWidth() - $margin * 2;
        $this->setMargins($margin, $margin, $margin);
        $width = $pw * 0.45;
        $left = $margin;
        $right = $margin + $width + $pw * 0.1;
        $this->SetFont('Roboto', 'Bold', 18);
        $y = $this->GetY();
        $y = $this->setTextCell($margin, $pw, 9, $y, JText::_('INVOICE'), 'L');
        $this->SetFont('Roboto', 'Regular', 10);
        $this->SetTextColor(149, 149, 149);
        $this->setTextCell($left, $width, 10, $y, 'No. '.$item->order_number, 'L');
        $date = JDate::getInstance($item->date)->format(gridboxHelper::$website->date_format);
        $y = $this->setTextCell($right, $width, 10, $y, $date, 'R');
        $y = $this->setTextCell($left, $pw, 8, $y, ' ', 'L');
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Roboto', 'Bold', 10);
        $this->setTextCell($left, $width, 10, $y, JText::_('FROM'), 'L');
        $y = $y1 = $this->setTextCell($right, $width, 10, $y, JText::_('BILLED_TO'), 'L');
        $this->SetFont('Roboto', 'Regular', 10);
        $this->SetTextColor(119, 119, 119);
        $address = array($general->business_name);
        $array = array();
        if (!empty($general->country)) {
            $array[] = $general->country;
        }
        if (!empty($general->region)) {
            $array[] = $general->region;
        }
        $address[] = implode(', ', $array);
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
        $address[] = implode(', ', $array);
        $address[] = $general->email;
        $address[] = $general->phone;
        foreach ($address as $value) {
            $y = $this->setTextCell($left, $width, 8, $y, $value, 'L');
        }
        foreach ($item->contact_info as $object) {
            if (!empty($object->title) && !empty($object->items)) {
                $this->SetTextColor(0, 0, 0);
                $this->SetFont('Roboto', 'Bold', 10);
                $y1 = $this->setTextCell($right, $width, 4, $y1, ' ', 'L');
                $y1 = $this->setTextCell($right, $width, 8, $y1, $object->title, 'L');
            }
            $this->SetTextColor(119, 119, 119);
            $this->SetFont('Roboto', 'Regular', 10);
            foreach ($object->items as $info) {
                $value = $info->value;
                if ($info->type == 'country') {
                    $object = json_decode($value);
                    $values = array($object->region, $object->country);
                    $value = implode(', ', $values);
                }
                $value = str_replace('; ', ', ', $value);
                $y1 = $this->setTextCell($right, $width, 8, $y1, $value, 'L');
            }
        }
        if ($y1 > $y) {
            $y = $y1;
        }
        $y = $this->setTextCell($left, $pw, 8, $y, ' ', 'L');
        $this->SetFillColor(239, 239, 239);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Roboto', 'Bold', 10);
        $header = array(JText::_('NAME'), JText::_('DESCRIPTION'), JText::_('QTY'));
        if (!empty($item->tax) && $item->tax_mode == 'incl') {
            $header[] = JText::_('NET_PRICE');
        }
        $header[] = JText::_('PRICE');
        $header[] = JText::_('AMOUNT');
        $n = count($header);
        $tw = array($pw * 0.3, $pw * 0.3, $pw * 0.1, $pw * 0.15, $pw * 0.15);
        if (!empty($item->tax) && $item->tax_mode == 'incl') {
            $tw = array($pw * 0.25, $pw * 0.2, $pw * 0.1, $pw * 0.15, $pw * 0.15, $pw * 0.15);
        }
        $tl = array();
        foreach ($tw as $i => $value) {
            $tLeft = 0;
            for ($j = 0; $j < $i; $j++) {
                $tLeft += $tw[$j];
            }
            $tl[] = $tLeft;
        }
        $ys = array();
        for ($i = 0; $i < $n; $i++) {
            $ys[] = $this->setTextCell($tl[$i] + $margin, $tw[$i], 10, $y, $header[$i], 'L', true);
        }
        $max = max($ys);
        $min = min($ys);
        if ($min != $max) {
            for ($i = 0; $i < $n; $i++) {
                if ($ys[$i] != $max) {
                    $this->setTextCell($tl[$i] + $margin, $tw[$i], $max - $ys[$i], $ys[$i], ' ', 'L', true);
                }
            }
        }
        $y = $max;
        $this->SetFont('Roboto', 'Regular', 10);
        $this->SetTextColor(119, 119, 119);
        $taxes = array();
        foreach($item->products as $product) {
            $y = $this->setTextCell($left, $pw, 5, $y, ' ', 'L');
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
            $priceText = $this->preparePrice($price, $item->currency_symbol, $item->currency_position);
            $info = array();
            foreach ($product->variations as $variation) {
                $info[] = $variation->title.' '.$variation->value;
            }
            $infoStr = implode('/', $info);
            $amountText = $this->preparePrice($amount, $item->currency_symbol, $item->currency_position);
            $ys = array();
            $texts = array($product->title, $infoStr, $product->quantity);
            if (!empty($item->tax) && $item->tax_mode == 'incl') {
                $netPrice = $price - ($price - $price / ($product->tax_rate / 100 + 1));
                $netText = $this->preparePrice($netPrice, $item->currency_symbol, $item->currency_position);
                $texts[] = $netText;
            }
            $texts[] = $priceText;
            $texts[] = $amountText;
            foreach ($tw as $i => $value) {
                $ys[] = $this->setTextCell($tl[$i] + $margin, $tw[$i], 5, $y, $texts[$i], 'L');
            }
            $y = max($ys);
            if (isset($product->extra_options->items)) {
                foreach ($product->extra_options->items as $extra) {
                    $this->SetTextColor(0, 0, 0);
                    $this->SetFont('Roboto', 'Bold', 10);
                    $y = $this->setTextCell($tl[1] + $margin, $tw[1], 5, $y, $extra->title, 'L');
                    $this->SetFont('Roboto', 'Regular', 10);
                    $this->SetTextColor(119, 119, 119);
                    $count = 0;
                    foreach ($extra->values as $value) {
                        $ys = array();
                        $ys[] = $this->setTextCell($tl[1] + $margin, $tw[1], 5, $y, $value->value, 'L');
                        $i = count($tw) - 1;
                        if ($value->price != '') {
                            $price = $value->price * $product->quantity;
                            $price = $this->preparePrice($price, $item->currency_symbol, $item->currency_position);
                        } else {
                            $price = '';
                        }
                        $ys[] = $this->setTextCell($tl[$i] + $margin, $tw[$i], 5, $y, $price, 'L');
                        $y = max($ys);
                        $count++;
                    }
                    if ($count < $product->extra_options->count) {
                        $y = $this->setTextCell($tl[1] + $margin, $tw[1], 5, $y, ' ', 'L');
                    }
                }
            }
        }
        $taxCount = count($taxes);
        $y = $this->setTextCell($left, $pw, 5, $y, ' ', 'L');
        $this->Line($margin, $y, $pw + $margin, $y);
        $y = $this->setTextCell($left, $pw, 8, $y, ' ', 'L');
        $left = $pw * 0.6 + $margin;
        $w = $pw * 0.25;
        $w1 = $pw * 0.15;
        $right = $left + $w;
        $this->SetFont('Roboto', 'Bold', 10);
        $this->SetTextColor(0, 0, 0);
        $y = $this->setCartTotal($left, $w, $right, $w1, $y, JText::_('SUBTOTAL'), $item->subtotal, $item);
        $this->SetFont('Roboto', 'Regular', 10);
        $this->SetTextColor(119, 119, 119);
        if ($item->promo) {
            $y = $this->setCartTotal($left, $w, $right, $w1, $y, JText::_('DISCOUNT'), $item->promo->value, $item, true);
        }
        if ($item->shipping) {
            $price = $item->shipping->type != 'free' && $item->shipping->type != 'pickup' ? $item->shipping->price : JText::_('FREE');
            $y = $this->setCartTotal($left, $w, $right, $w1, $y, JText::_('SHIPPING'), $price, $item);
        }
        if ($item->shipping && !empty($item->shipping->tax) && $item->tax_mode == 'incl') {
            $this->SetFontSize(7);
            $price = $this->preparePrice($item->shipping->tax, $item->currency_symbol, $item->currency_position);
            $y = $this->setTextCell($left, $w + $w1, 5, $y, JText::_('INCLUDES').' '.$item->shipping->tax_title.' '.$price, 'L');
            $item->tax = $item->tax * 1 + $item->shipping->tax;
            if ($taxCount == 1) {
                foreach ($taxes as $tax) {
                    if ($tax->title != $item->shipping->tax_title || $tax->rate != $item->shipping->tax_rate) {
                        $taxCount++;
                    }
                }
            }
            $this->SetFontSize(10);
        } else if ($item->shipping && !empty($item->shipping->tax)) {
            $y = $this->setCartTotal($left, $w, $right, $w1, $y, JText::_('TAX_ON_SHIPPING'), $item->shipping->tax, $item);
        }
        if (!empty($item->tax) && $item->tax_mode == 'excl' && $taxCount == 0) {
            $y = $this->setCartTotal($left, $w, $right, $w1, $y, JText::_('TAX'), $item->tax, $item);
        } else if (!empty($item->tax) && $item->tax_mode == 'excl') {
            foreach ($taxes as $tax) {
                $y = $this->setCartTotal($left, $w, $right, $w1, $y, $tax->title, $tax->amount, $item);
            }
        }
        $y = $this->setTextCell($left, $pw, 8, $y, ' ', 'L');
        $this->SetFont('Roboto', 'Bold', 10);
        $this->SetTextColor(0, 0, 0);
        $y = $this->setCartTotal($left, $w, $right, $w1, $y, JText::_('TOTAL'), $item->total, $item);
        if (!empty($item->tax) && $item->tax_mode == 'incl') {
            $this->SetFont('Roboto', 'Regular', 7);
            $this->SetTextColor(119, 119, 119);
            $text = $taxCount == 1 ? JText::_('INCLUDES').' '.$taxes[0]->rate.'% '.$taxes[0]->title : JText::_('INCLUDING_TAXES');
            $price = $this->preparePrice($item->tax, $item->currency_symbol, $item->currency_position);
            $y = $this->setTextCell($left, $w + $w1, 5, $y, $text.' '.$price, 'L');
        }
        $path .= 'order-'.str_replace('#', '', $item->order_number).'.pdf';
        $this->Output($dest, $path);

        return $path;
    }
}