<?php if(!class_exists('Rain\Tpl')){exit;}?><form method="post" action="https://pagseguro.uol.com.br/v2/checkout/payment.html">  
          
        <!-- Campos obrigatórios -->  
        <input name="receiverEmail" type="hidden" value="gabrielaarend@yahoo.com.br">     <!-- identifica o cliente no pagseguro -->
        <input name="currency" type="hidden" value="BRL">  
        
        <?php $counter1=-1;  if( isset($products) && ( is_array($products) || $products instanceof Traversable ) && sizeof($products) ) foreach( $products as $key1 => $value1 ){ $counter1++; ?> 
        <input name="itemId<?php echo htmlspecialchars( $counter1+1, ENT_COMPAT, 'UTF-8', FALSE ); ?>" type="hidden" value="<?php echo htmlspecialchars( $value1["idproduct"], ENT_COMPAT, 'UTF-8', FALSE ); ?>">  
        <input name="itemDescription<?php echo htmlspecialchars( $counter1+1, ENT_COMPAT, 'UTF-8', FALSE ); ?>" type="hidden" value="<?php echo htmlspecialchars( $value1["desproduct"], ENT_COMPAT, 'UTF-8', FALSE ); ?>">  
        <input name="itemAmount<?php echo htmlspecialchars( $counter1+1, ENT_COMPAT, 'UTF-8', FALSE ); ?>" type="hidden" value="<?php echo htmlspecialchars( $value1["vltotal"], ENT_COMPAT, 'UTF-8', FALSE ); ?>">  
        <input name="itemQuantity<?php echo htmlspecialchars( $counter1+1, ENT_COMPAT, 'UTF-8', FALSE ); ?>" type="hidden" value="<?php echo htmlspecialchars( $value1["nrqtd"], ENT_COMPAT, 'UTF-8', FALSE ); ?>">  
        <input name="itemWeight<?php echo htmlspecialchars( $counter1+1, ENT_COMPAT, 'UTF-8', FALSE ); ?>" type="hidden" value="<?php echo htmlspecialchars( $value1["vlweight"]*1000, ENT_COMPAT, 'UTF-8', FALSE ); ?>">  
        <?php } ?>

        <!-- Faltou programar o val do frete
        <input name="itemId3" type="hidden" value="3">  
        <input name="itemDescription3" type="hidden" value="Frete">  
        <input name="itemAmount3" type="hidden" value="100.00">  
        <input name="itemQuantity3" type="hidden" value="1">  
        <input name="itemWeight3" type="hidden" value="0">          
        -->

        <input name="reference" type="hidden" value="<?php echo htmlspecialchars( $order["idorder"], ENT_COMPAT, 'UTF-8', FALSE ); ?>">  
          
        <!-- Informações de frete (opcionais) -->  
        <input name="shippingType" type="hidden" value="1">
        <input name="shippingAddressPostalCode" type="hidden" value="<?php echo htmlspecialchars( $order["deszipcode"], ENT_COMPAT, 'UTF-8', FALSE ); ?>">  
        <input name="shippingAddressStreet" type="hidden" value="<?php echo utf8_encode($order["desaddress"]); ?>">  
        <input name="shippingAddressNumber" type="hidden" value="<?php echo utf8_encode($order["desnumber"]); ?>">  
        <input name="shippingAddressComplement" type="hidden" value="<?php echo utf8_encode($order["descomplement"]); ?>">  
        <input name="shippingAddressDistrict" type="hidden" value="<?php echo utf8_encode($order["desdistrict"]); ?>">  
        <input name="shippingAddressCity" type="hidden" value="<?php echo utf8_encode($order["descity"]); ?>">
        <input name="shippingAddressState" type="hidden" value="<?php echo utf8_encode($order["desstate"]); ?>"> 
        <input name="shippingAddressCountry" type="hidden" value="<?php echo utf8_encode($order["descountry"]); ?>">  
  
        <!-- Dados do comprador (opcionais) -->  
        <input name="senderName" type="hidden" value="<?php echo utf8_encode($order["desperson"]); ?>">  
        <input name="senderAreaCode" type="hidden" value="<?php echo htmlspecialchars( $phone["areaCode"], ENT_COMPAT, 'UTF-8', FALSE ); ?>">  
        <input name="senderPhone" type="hidden" value="<?php echo htmlspecialchars( $phone["number"], ENT_COMPAT, 'UTF-8', FALSE ); ?>">  
        <input name="senderEmail" type="hidden" value="<?php echo htmlspecialchars( $order["desemail"], ENT_COMPAT, 'UTF-8', FALSE ); ?>">  
          
</form>  </form>  <!-- No script abaixo pega o unico formulario que tem na tela e manda -->

<script type="text/javascript">
document.forms[0].submit();
</script>