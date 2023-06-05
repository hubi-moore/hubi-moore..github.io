<?php

class Network_ProductSubstitutes_IndexController extends Mage_Core_Controller_Front_Action
{
    public function getsubstitutesAction()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $groupid = $customer->getGroupId();
        $saphelper = Mage::helper('sap/products');
        $productCollection = Mage::getModel('catalog/product');
        $post = $this->getRequest()->getPost();
        $params = $this->getRequest()->getParams();
        $zamiennikiSKUs = trim(base64_decode($post['zam']));
        $zamiennikiSKUsArray = explode(', ', $zamiennikiSKUs);
        $mainProd = $productCollection->load($params['product_data']);
        $response = array(
            'data_provided' => $params,
            'product_data' => $params['product_data'],
        );
        $mainProd = $productCollection->load($params['product_data']);
        $iwopak = -1;
        if (substr($mainProd->getSku(), -1) == "K") {
            $iwopak = floor($mainProd->getData("orientacyjnie_sztuk_w_opak") * $mainProd->getWielkoscOpakowania());
        } else if ($mainProd->getAttributeText('jednostka_miary') === 'SZT' || $mainProd->getAttributeText('jednostka_miary') === 'TYS') {
            $iwopak = $mainProd->getWielkoscOpakowania();
        }
        if ($iwopak > 0) {
            $iwopak_txt = $iwopak;
        } else {
            $iwopak_txt = 'n/d';
        }

        $mainProdCheck = array(
            'size' => round($mainProd->getData('wielkosc_opakowania'), 3),
            'jm' => $mainProd->getAttributeText('jednostka_miary'),
            'rozmiary' => $mainProd->getRozmiary(),
            'klasa' => $mainProd->getAttributeText('pimcore_klasa'),
            'pokrycie' => $mainProd->getAttributeText('pimcore_rodzaj_pokrycia'),
            'sku' => $mainProd->getCustomSku(),
            'iwopak' => $iwopak_txt,
            'id' => $mainProd->getId(),
        );
        $linkURL = $params['prod_url'];
        $preLink = explode('?', $linkURL)[0];
        $showProductLink = false;
        $wszystkieZamienniki = array();
        $returnHTML = '';
        $returnHTMLHeader = '';
        $returnHTMLBody = $returnHTMLBodyInner = '';


        foreach ($zamiennikiSKUsArray as $sku) {
            $products = $productCollection->getCollection()->addAttributeToSelect('*')
                ->addAttributeToFilter(
                    array(
                        array('attribute' => 'custom_sku_q', 'like' => "$sku\_%"),
                        array('attribute' => 'sku', 'eq' => "$sku")
                    )
                );

            $wszystkieZamienniki[$sku] = array();
            $klasaFlag = false;
            $pokrycieFlag = false;
            foreach ($products as $product) {
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                if (round($stock->getQty(), 2) > 0) {
                    $wszystkieZamienniki[$sku][] = array(
                        'size' => $product->getWielkoscOpakowania(),
                        'rozmiary' => $product->getRozmiary(),
                        'klasa' => $product->getAttributeText('pimcore_klasa'),
                        'pokrycie' => $product->getAttributeText('pimcore_rodzaj_pokrycia'),
                        'sku' => $product->getCustomSku(),
                        'id' => $product->getId(),
                        'qty' => round($stock->getQty(), 2),
                        'item' => $product);
                }
                if (strlen($product->getAttributeText('pimcore_klasa')) > 0) {
                    $klasaFlag = true;
                }
                if (strlen($product->getAttributeText('pimcore_rodzaj_pokrycia')) > 0) {
                    $pokrycieFlag = true;
                }
            }
            $response['data_items'] = $wszystkieZamienniki;

        }
        $somethingIsDifferent = false;
        foreach ($wszystkieZamienniki as $zgrupowane) {
            $product = $zgrupowane[0]['item'];
            if (!$product)
                continue;
            $qty = $zgrupowane[0]['qty'];
            $setid = $product->getAttributeSetId();
            $jmPrzedRabatem = $saphelper->getCenaJednostkowaPrzedRabatem($product);
            $jmPoRabacie = $saphelper->getCenaJednostkowaPoRabacieByCustomerId($product, $customer->getId());
            $opakowanieZbiorcze = $product->getOpakowaniaZbiorcze();
            $finalPrice = '<td class="a-center" sorttable_customkey="' . $product->getFinalPrice() . '"><span class="hidden-small">Cena opakowania:</span>';
            $ileWopakPre = '';
            $ileWOpakCheck = null;
            $stockQty = '<td><span class="hidden-small">Stan magazynowy:</span>';
            $buttonOrder = '<td class="zam-item-order">';
            if (count($zgrupowane) > 1) {
                if ($to_cut = strpos($zgrupowane[0]['sku'], '_'))
                    $boxSizes = "<select id='" . substr($zgrupowane[0]['sku'], 0, $to_cut) . "'>";
                else
                    $boxSizes = "<select id='" . $zgrupowane[0]['sku'] . "'>";

                $counter = 0;
                $counterPrice = 0;
                $counterStock = 0;
                $counterOrder = 0;
                $selected = "selected='selected'";
                $sizes = array();
                foreach ($zgrupowane as $paczka) {
                    if (in_array($paczka['size'], $sizes) == false) {
                        $sizes[] = $paczka['size'];
                    } else {
                        continue;
                    }
                    $boxSizes .= "<option " . $selected;
                    if ($counter == 0) {
                        $selected = '';
                        $counter = 1;
                    }
                    $boxSizes .= " value='" . $paczka['id'] . "'>" . $paczka['size'] . "</option>";
                    if (substr($product->getSku(), -1) == "K") {
                        $iloscWOpakowaniu = floor($paczka['item']->getData("orientacyjnie_sztuk_w_opak") * $paczka['item']->getWielkoscOpakowania());
                    } else if ($paczka['item']->getAttributeText('jednostka_miary') === 'SZT' || $paczka['item']->getAttributeText('jednostka_miary') === 'TYS') {
                        $iloscWOpakowaniu = $paczka['item']->getWielkoscOpakowania();
                    }
                    if ($iloscWOpakowaniu > 0) {
                        $ilosc_w_opak_txt = $iloscWOpakowaniu;
                    } else {
                        $ilosc_w_opak_txt = 'n/d';
                    }

                    if ($counterPrice == 0) {
                        $finalPrice .= '<span id="price_' . $product->getSku() . '_' . $paczka['id'] . '" class="price_' . $product->getSku() . '" style="display: block;"><div class="price-box"><p class="special-price">';
                        #$finalPrice .= '<span class="price-label">Twoja cena: </span>';
                        $finalPrice .= '<span class="price" id="product-price-' . $paczka['id'] . '">' . number_format($paczka['item']->getGroupPrice(), 2, ',', '') . ' zł</span></p>';
                        //$finalPrice .= '<p style="color:#b5b5b5;font-weight: normal;font-size: 12px;font-family: Arial, sans-serif;" class="old-price"><span class="price-label">Cena katalogowa:</span> <span style="font-weight:400;" class="price" id="old-price-' . $paczka['id'] . '">' . number_format($paczka['item']->getPrice(), 2, ',', '') . ' zł</span></p></div>';
                        $finalPrice .= "</span>";
                        $ileWopakPre .= '<span id="jm_opak_' . $product->getSku() . '_' . $paczka['id'] . '" class="jm_opak_' . $product->getSku() . '" style="display: inline-block;">' . $ilosc_w_opak_txt . '</span>';
                        $counterPrice++;
                        $ileWOpakCheck = $ilosc_w_opak_txt;
                    } else {
                        $finalPrice .= '<span id="price_' . $product->getSku() . '_' . $paczka['id'] . '" class="price_' . $product->getSku() . '" style="display: none;"><div class="price-box"><p class="special-price">';
                        #$finalPrice .= '<span class="price-label">Twoja cena: </span>';
                        $finalPrice .= '<span class="price" id="product-price-' . $paczka['id'] . '">' . number_format($paczka['item']->getGroupPrice(), 2, ',', '') . ' zł</span></p>';
                        //$finalPrice .= '<p style="color:#b5b5b5;font-weight: normal;font-size: 12px;font-family: Arial, sans-serif;" class="old-price"><span class="price-label">Cena katalogowa:</span> <span style="font-weight:400;" class="price" id="old-price-' . $paczka['id'] . '">' . number_format($paczka['item']->getPrice(), 2, ',', '') . ' zł</span></p></div>';
                        $finalPrice .= "</span>";
                        $ileWopakPre .= '<span id="jm_opak_' . $product->getSku() . '_' . $paczka['id'] . '" class="jm_opak_' . $product->getSku() . '" style="display: none;">' . $iloscWOpakowaniu . '</span>';
                    }


                    $stockQty .= '<span id="stock_' . $product->getSku() . '_' . $paczka['id'] . '" class="stock_' . $product->getSku() . '"';
                    if ($counterStock == 0) {
                        $stockQty .= "style='display: block'";
                        $counterStock++;
                    } else {
                        $stockQty .= "style='display: none'";
                    }
                    $stockQty .= '>' . $paczka['qty'] . '</span>';

                    if ($counterOrder == 0) {
                        $buttonOrder .= '<div id="order_' . $product->getSku() . '_' . $paczka['id'] . '" class="btn-order-container order_' . $product->getSku() . '" style="display: block;"><button type="button" title="Dodaj do koszyka" class="button btn-cart btn-cart-zam" data-add-link="' . Mage::helper('checkout/cart')->getAddUrl($paczka['item']) . '"><span><span></span></span></button></div>';
                        $counterOrder++;
                    } else {
                        $buttonOrder .= '<div id="order_' . $product->getSku() . '_' . $paczka['id'] . '" class="btn-order-container order_' . $product->getSku() . '" style="display: none;"><button type="button" title="Dodaj do koszyka" class="button btn-cart btn-cart-zam" data-add-link="' . Mage::helper('checkout/cart')->getAddUrl($paczka['item']) . '"><span><span></span></span></button></div>';
                    }
                }
                $boxSizse .= "</select>";
            } else {
                $boxSizes = round($product->getData('wielkosc_opakowania'), 3);
                $buttonOrder .= '<div id="order_' . $product->getSku() . '_' . $product->getId() . '" class="btn-order-container" style="display: block;"><button type="button" title="Dodaj do koszyka" class="button btn-cart btn-cart-zam" data-add-link="' . Mage::helper('checkout/cart')->getAddUrl($product) . '"><span><span></span></span></button></div>';
                $stockQty .= '<span class="stock_' . $product->getSku() . '">' . $qty . '</span>';
                $finalPrice .= '<span class="price_' . $product->getSku() . '" style="display: block;"><div class="price-box"><p class="special-price">';
                #$finalPrice .= '<span class="price-label">Twoja cena: </span>';
                $finalPrice .= '<span class="price">' . number_format($product->getGroupPrice(), 2, ',', '') . ' zł</span></p>';
                //$finalPrice .= '<p style="color:#b5b5b5;font-weight: normal;font-size: 12px;font-family: Arial, sans-serif;" class="old-price"><span class="price-label">Cena katalogowa:</span> <span style="font-weight:400;" class="price">' . number_format($product->getPrice(), 2, ',', '') . ' zł</span></p></div>';
                $finalPrice .= "</span>";
                if (substr($product->getSku(), -1) == "K") {
                    $iloscWOpakowaniu = floor($product->getData("orientacyjnie_sztuk_w_opak") * $product->getWielkoscOpakowania());
                } else if ($product->getAttributeText('jednostka_miary') === 'SZT' || $product->getAttributeText('jednostka_miary') === 'TYS') {
                    $iloscWOpakowaniu = $product->getWielkoscOpakowania();
                }
                if ($iloscWOpakowaniu > 0) {
                    $ilosc_w_opak_txt = $iloscWOpakowaniu;
                } else {
                    $ilosc_w_opak_txt = 'n/d';
                }
                $ileWOpakCheck = $ilosc_w_opak_txt;
                $opakowanieZbiorczeTooltip = $product->getOpakowanieTooltip();
                $check_sku = explode('_', $product->getSku());
                $check_jm = $product->getAttributeText('jednostka_miary');
                $condition = ($check_jm == 'OPK' && (strpos($check_sku[0], 'QO') !== false));
                if ($condition === true) {
                    $ilosc_w_opak_txt = $product->getWielkoscOpakowania() * intval($product->getData("orientacyjnie_sztuk_w_opak"));
                }
                if ((strlen($product->getData('orientacyjnie_sztuk_w_opak')) === 0) && ($iloscWOpakowaniu <= 0)) {
                    $ilosc_w_opak_txt = 'n/d';
                    $opakowanieZbiorczeTooltip = '';
                }
                $ileWopakPre .= '<span id="jm_opak_' . $product->getSku() . '_' . $product->getId() . '" class="jm_opak_' . $product->getSku() . '" style="display: inline-block;">' . $ilosc_w_opak_txt . ' ' . $opakowanieZbiorczeTooltip . '</span>';

            }
            $finalPrice .= '</td>';
            $classTd = '';
            if ($mainProdCheck['iwopak'] !== $ileWOpakCheck) {
                $somethingIsDifferent = true;
                $classTd = 'item_differ';
            }
            $ileWopak = '<td class="a-center ' . $classTd . '"><span class="hidden-small">Ilość sztuk w opakowaniu</span>' . $ileWopakPre . '</td>';
            $stockQty .= '</td>';
            $buttonOrder .= '</td>';
            $itemRozm = null;
            if ($setid == 9) { //Sruby i wkrety
                //$rozm = '<span style="text-decoration:underline;">Rozmiar</span>' . "\n";
                $rozm = '<p><b>' . $product->getRozmiary() . '</b></p>';
                $itemRozm = $product->getRozmiary();
                $mainProdCheck['rozmiary'] = $mainProd->getRozmiary();
            } else if ($setid == 16) { //Brzeszczoty
                $rozm = '<span style="text-decoration:underline;">Uzębienie [mm] / Symbol / Oznaczenie</span>' . "\n";
                $rozm .= '<p><b>' . $product->getAttributeText('brzeszczot_uzebienie') . ' / ' . $product->getData('brzeszczot_symbol') . ' / ' . $product->getData('brzeszczot_oznaczenie') . '</b></p>';
                $itemRozm = $product->getAttributeText('brzeszczot_uzebienie') . ' / ' . $product->getData('brzeszczot_symbol') . ' / ' . $product->getData('brzeszczot_oznaczenie');
                $mainProdCheck['rozmiary'] = $mainProd->getAttributeText('brzeszczot_uzebienie') . ' / ' . $mainProd->getData('brzeszczot_symbol') . ' / ' . $mainProd->getData('brzeszczot_oznaczenie');
            } else if ($setid == 19) { //Elektrody i druty
                //$rozm = '<span style="text-decoration:underline;">d (mm) x L (mm)</span>' . "\n";
                $rozm = '<p><b>' . $product->getRozmiary() . '</b></p>';
                $itemRozm = $product->getRozmiary();
                $mainProdCheck['rozmiary'] = $mainProd->getRozmiary();
            } else if ($setid == 12) {//Gwozdzie
                //$rozm = '<span style="text-decoration:underline;">d (mm) x L (mm)</span>' . "\n";
                $rozm = '<p><b>' . $product->getRozmiary() . '</b></p>';
                $itemRozm = $product->getRozmiary();
                $mainProdCheck['rozmiary'] = $mainProd->getRozmiary();
            } else if ($setid == 18) { //Kotwy i łączniki
                //$rozm = '<span style="text-decoration:underline;">Rozmiar D x Rozmiar L x Rozmiar d (mm)</span>' . "\n";
                $rozm = '<p><b>' . $product->getRozmiary() . '</b></p>';
                $itemRozm = $product->getRozmiary();
                $mainProdCheck['rozmiary'] = $mainProd->getRozmiary();
            } else if ($setid == 10) { //Nakretki i podkladki
                //$rozm = '<span style="text-decoration:underline;">Rozmiar D x Rozmiar d (mm)</span>' . "\n";
                $rozm = '<p><b>' . $product->getRozmiary() . '</b></p>';
                $itemRozm = $product->getRozmiary();
                $mainProdCheck['rozmiary'] = $mainProd->getRozmiary();
            } else if ($setid == 15) { //Narzedzia reczne
                //$rozm = '<span style="text-decoration:underline;">Rozmiar D x Rozmiar d (mm)</span>' . "\n";
                $rozm = '<p><b>' . $product->getRozmiary() . '</b></p>';
                $itemRozm = $product->getRozmiary();
                $mainProdCheck['rozmiary'] = $mainProd->getRozmiary();
            } else if ($setid == 17) { //Odzież
                //$rozm = '<span style="text-decoration:underline;">Rozmiar</span>' . "\n";
                $rozm = '<p><b>' . $product->getRozmiary() . '</b></p>';
                $itemRozm = $product->getRozmiary();
                $mainProdCheck['rozmiary'] = $mainProd->getRozmiary();
            } else if ($setid == 13) { //Tarcze i arkusze
                //$rozm = '<span style="text-decoration:underline;">Rozmiar</span>' . "\n";
                $rozm = '<p><b>' . $product->getRozmiary() . '</b></p>';
                $itemRozm = $product->getRozmiary();
                $mainProdCheck['rozmiary'] = $mainProd->getRozmiary();
            } else if ($setid == 14) { //Wiertla
                //$rozm = '<span style="text-decoration:underline;">Średnica x Rozmiar L x Rozmiar L2</span>' . "\n";
                $rozm = '<p><b>' . $product->getRozmiary() . '</b></p>';
                $itemRozm = $product->getRozmiary();
                $mainProdCheck['rozmiary'] = $mainProd->getRozmiary();
            } else if ($setid == 20) { //Chemia
                $rozm = '<span style="text-decoration:underline;">Pojemność (ml)</span>' . "\n";
                $rozm .= '<p><b>' . $product->getAttributeText('pojemnosc_ml') . '</b></p>';
                $itemRozm = $product->getAttributeText('pojemnosc_ml');
                $mainProdCheck['rozmiary'] = $mainProd->getAttributeText('pojemnosc_ml');
            } else {
                $rozm = '<span style="text-decoration:underline;">Brak rozmiaru</span>' . "\n";
                $rozm = '<p><b>n/a</b></p>';
                $itemRozm = 'Brak rozmiaru';
                $mainProdCheck['rozmiary'] = 'Brak rozmiaru';
            }

            $groupParentsIds = Mage::getResourceSingleton('catalog/product_link')->getParentIdsByChild($product->getId(), Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED);
            if ($groupParentsIds != '' && count($groupParentsIds) > 0) {
                $flag = true;
                foreach ($groupParentsIds as $parentId) {
                    $flag = false;
                    $groupedProduct = Mage::getModel('catalog/product')->load($parentId);
                    $groupParentsLink = $groupedProduct->getProductUrl();
                    $imageUrl = Mage::helper('catalog/image')->init($groupedProduct, 'small_image');
                    if (!flag) {
                        break;
                    }
                }
            }

            $header = '<tr>' . "\n";

            switch (substr($product->getSku(), -1)) {
                case 'T':
                    $jm = 'TYS';

                    break;
                case 'S':
                    $jm = 'SZT';
                    break;
                case 'O':
                    $jm = 'OPK';

                    break;
                case 'K':
                    $jm = 'KG';

                    break;
                case 'P':
                    $jm = 'KPL';
                    break;
                case 'M':
                    $jm = 'MB';
                    break;

                default:
                    $jm = 'SZT';
            }
            $header .= '<td class="zam-item-info"><div class="item_firstattributes"><div class="attributes_hidden"><span class="attrhidden_tool">i</span><div class="attrhidden_content">';
            $header .= '<div><b>Nr produktu: </b><span>' . $product->getSku() . '</span></div>';
            if (empty($product->getPimcoreNumberq()) == false) {
                $header .= '<div><b>Nr Q: </b><span>' . $product->getPimcoreNumberq() . '</span></div>';
            }
            $header .= '<div><b>Nazwa: </b>' . $product->getName() . '</div>';
            if (substr($product->getSku(), -1) == "K") {
                $iloscWOpakowaniu = floor($product->getData("orientacyjnie_sztuk_w_opak") * $product->getWielkoscOpakowania());
            } else if ($jm === 'SZT' || $jm === 'TYS') {
                $iloscWOpakowaniu = $product->getWielkoscOpakowania();
            }

            if (substr($product->getSku(), -1) == "K") {
                if ($iloscWOpakowaniu > 0) {
                    $header .= '<div><b>~Ilość szt. w opakowaniu: </b><span>' . $iloscWOpakowaniu . '</span></div>';
                }
            } else {
                if (empty($product->getData('waga')) == false) {
                    $wagaTxt = round($product->getData('waga'), 2);
                    $header .= '<div><b>Waga opakowania: </b><span>' . $wagaTxt . ' KG</span></div>';
                }
            }
            $header .= '<div class="item-attr_jednostka_miary"><b>JM: </b><span>' . $product->getAttributeText('jednostka_miary') . '</span></div>';
            $header .= '</div></div></div></td>';
            $classTd = '';
            if ($mainProdCheck['rozmiary'] !== $itemRozm) {
                $somethingIsDifferent = true;
                $classTd = 'item_differ';
            }
            $header .= '<td class="' . $classTd . '">' . $rozm . '</td>' . "\n";
            if ($klasaFlag === true) {
                $classTd = '';
                if ($mainProdCheck['klasa'] !== $product->getAttributeText('pimcore_klasa')) {
                    $somethingIsDifferent = true;
                    $classTd = 'item_differ';
                }
                $header .= '<td class="' . $classTd . '">' . $product->getAttributeText('pimcore_klasa') . '</td>' . "\n";
            }
            if ($pokrycieFlag === true) {
                $classTd = '';
                if ($mainProdCheck['pokrycie'] !== $product->getAttributeText('pimcore_rodzaj_pokrycia')) {
                    $somethingIsDifferent = true;
                    $classTd = 'item_differ';
                }
                $header .= '<td class="' . $classTd . '">' . $product->getAttributeText('pimcore_rodzaj_pokrycia') . '</td>' . "\n";
            }
            $header .= $ileWopak . "\n";
            $classTd = '';
            if ($mainProdCheck['size'] !== $boxSizes) {
                $somethingIsDifferent = true;
                $classTd = 'item_differ';
            }
            $header .= '<td class="' . $classTd . '">' . $boxSizes . '</td>' . "\n";
            $classTd = '';
            if ($mainProdCheck['jm'] !== $product->getAttributeText('jednostka_miary')) {
                $somethingIsDifferent = true;
                $classTd = 'item_differ';
            }
            $header .= '<td class="' . $classTd . '">' . $product->getAttributeText('jednostka_miary') . '</td>' . "\n";
            $header .= $stockQty . "\n";
            $header .= '<td class="hiddenprice a-center jm-cena" sorttable_customkey="' . $jmPoRabacie . '"><span class="hidden-small">Cena JM:</span> <span class="twoja-cena-jm">' . number_format($jmPoRabacie, 2, ',', '') . ' zł</span></td>' . "\n";
            $header .= $finalPrice . "\n";
            $header .= '<td class="a-center" data-opakowanie_zbiorcze="' . $opakowanieZbiorcze . '"> <input id="super_' . $product->getCustomSku() . '" type="text" name="super_group[' . $product->getId() . ']" maxlength="12" value="0" title="Ilość" class="input-text qty" style="width:50px !important;"/></td>' . "\n";
            $header .= $buttonOrder;
            if ($groupParentsLink != '' && $preLink !== $groupParentsLink) {
                $showProductLink = true;
                $header .= '<td class="zam-item-parent"><button type="button" title="Przejdź do produktu" class="button btn-product" ><a href="' . $groupParentsLink . '"><span>Szczegóły</span></a></button></td>';
            }
            $header .= '</tr>' . "\n";
            $returnHTMLBodyInner .= $header;
        }
        $returnHTMLHeaderInner = '';
        $returnHTMLHeaderInner .= '<tr>' . "\n";
        $returnHTMLHeaderInner .= '<th></th>' . "\n";
        $returnHTMLHeaderInner .= '<th>Rozmiary</th>' . "\n";
        if ($klasaFlag === true) {
            $returnHTMLHeaderInner .= '<th>Klasa</th>' . "\n";
        }
        if ($pokrycieFlag === true) {
            $returnHTMLHeaderInner .= '<th>Rodzaj pokrycia</th>' . "\n";
        }
        $returnHTMLHeaderInner .= '<th>~Ilość Sztuk w opak.</th>' . "\n";
        $returnHTMLHeaderInner .= '<th>Wielkość<br>Opakowania</th>' . "\n";
        $returnHTMLHeaderInner .= '<th>JM</th>' . "\n";
        $returnHTMLHeaderInner .= '<th>Stan<br>Magazynowy</th>' . "\n";
        $returnHTMLHeaderInner .= '<th>Cena JM</th>' . "\n";
        $returnHTMLHeaderInner .= '<th>Cena opakowania</th>' . "\n";
        $returnHTMLHeaderInner .= '<th>Liczba<br>Opakowań</th>' . "\n";
        $returnHTMLHeaderInner .= '<th>Dodaj<br>do koszyka</th>' . "\n";
        if ($showProductLink === true) {
            $returnHTMLHeaderInner .= '<th>Produkt<br>główny</th>' . "\n";
        }
        $returnHTMLHeaderInner .= '</tr>' . "\n";
        $returnHTMLHeader .= '<thead>' . "\n";
        $returnHTMLHeader .= $returnHTMLHeaderInner;
        $returnHTMLHeader .= '</thead>' . "\n";
        $returnHTMLBody .= '<tbody>' . "\n";
        $returnHTMLBody .= $returnHTMLBodyInner;
        $returnHTMLBody .= '</tbody>' . "\n";
        $returnHTML .= '<table id="zamienniki-table">' . "\n";
        $returnHTML .= $returnHTMLHeader . $returnHTMLBody;
        $returnHTML .= '</table>' . "\n";


        $response['html'] = $returnHTML;
        $response['differ'] = $somethingIsDifferent;
        $this->_sendJson($response);
        return $response;
    }

    /**
     * send json respond
     *
     * @param array $response - response data
     */
    private function _sendJson($response)
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($response));
    }

    public function getdostawazewitemsAction()
    {
        $response = array();
        $params = $this->getRequest()->getPost();
        $response['params'] = $params;
        $this->_sendJson($response);
        return $response;
    }
}