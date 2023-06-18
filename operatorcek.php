<?php
echo "Masukkan Nomor : ";
$phone_number = trim(fgets(STDIN));

// daftar nomor awalan operator Indonesia
$telkomsel_prefix = array("0811", "0812", "0813", "0821", "0822", "0823", "0852", "0853", "0851");
$indosat_prefix = array("0814", "0815", "0816", "0855", "0856", "0857", "0858");
$xl_prefix = array("0859", "0877", "0878", "0817", "0818", "0819");
$tri_prefix = array("0898", "0899", "0895", "0896", "0897");
$smartfren_prefix = array("0889", "0881", "0882", "0883", "0886", "0887", "0888", "0884", "0885");
$axis_prefix = array("0832", "0833", "0838", "0831");

// mengambil empat digit awal nomor HP
$prefix = substr($phone_number, 0, 4);

// mencocokkan nomor awalan dengan daftar nomor awalan operator
if (in_array($prefix, $telkomsel_prefix)) {
    echo "Nomor HP $phone_number adalah Telkomsel";
} elseif (in_array($prefix, $indosat_prefix)) {
    echo "Nomor HP $phone_number adalah Indosat Ooredoo";
} elseif (in_array($prefix, $xl_prefix)) {
    echo "Nomor HP $phone_number adalah XL Axiata";
} elseif (in_array($prefix, $tri_prefix)) {
    echo "Nomor HP $phone_number adalah 3 / Tri";
} elseif (in_array($prefix, $smartfren_prefix)) {
    echo "Nomor HP $phone_number adalah Smartfren";
} elseif (in_array($prefix, $axis_prefix)) {
    echo "Nomor HP $phone_number adalah Axis";
} else {
    echo "Tidak dapat mengenali operator dari nomor HP $phone_number";
}
echo "\n";
?>
