<!--#set var="config" value="
debugEnabled = 1
apiUrlRegisterProd = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlRegisterDeb = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlGetStatusProd = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
apiUrlGetStatusDeb = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
login = yourlogin
password = yourpassword
productType = INSTALLMENT
productID = 10
includeShipping = 1
shippingProductName = Доставка
measure = шт.
onFailSetOrderStatus = rejected
onOkSetOrderStatus = confirmed
"-->

<!--#set var="result" value="
    ##if(url != '')##
    ##--url = ##url##--##
    <script type="text/javascript">
        document.location.replace("##url##");
    </script>
    ##else##
    Ошибка ##errCode##: ##errMsg##
    ##endif##
"-->
