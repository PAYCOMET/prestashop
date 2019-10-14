# Módulo de pago de PAYCOMET para Prestashop 1.7+


Ofrece la posibilidad de cobrar a tus clientes con tarjeta en tiendas Prestashop 1.7+.

## Documentación del Módulo.

En el siguiente enlace [Modulo Prestashop PAYCOMET](https://docs.paycomet.com/es/modulos-de-pago/prestashop)


## Instalación

Accedemos a al administración de Prestashop menú Modulos.
Pulsamos en Añadir nuevo modulo y seleccionamos el zip descargado.

## Configuración del Módulo

Rellenaremos los datos según la configuracion disponible en PAYCOMET. https://www.paycomet.com/ → Mis productos → configurar productos y seleccionamos el producto que vayamos a configurar en nuestra tienda Prestashop.


### Integración: 

#### Bankstore IFRAME/XML: La mejor opción para integrar el módulo y poder almacenar datos de tarjetas para futuras compras, además de ofrecer la posibilidad de suscribirse a productos.

- Terminales disponibles: Seleccione el tipo de terminal contratado.
- 3D Secure en la primera compra: Si la primera compra se realiza por terminal seguro. La opción NO sólo está disponible cuando el cliente tiene contratado un terminal no seguro. En el resto de casos la primera compra siempres se hará por 3D Secure.
- Usar 3D Secure en compras superiores a: Para solicitar la verificación 3DSecure en compras que superen el importe indicado.
- Solicitar contraseña del comercio en compras con tarjetas almacenadas: Se pide la contraseña del usuario en el comercio.
- Contraseña: La contraseña asignada al producto en PAYCOMET.
- Numero de terminal: El terminal asignado al producto en PAYCOMET.
- Codigo cliente: El codigo cliente asignado al producto en PAYCOMET.
- Activar suscripciones: Si se desea que se muestre o no la opción de suscribirse en los carros de compra.

IMPORTANTE: En la configuración del modulo se indican las URL OK, URL OK y URL de NOTIFICACION que deberán definirse en la configuración de PAYCOMET.


## Configuración del producto en PAYCOMET

Accedemos a nuestro area de clientes en https://www.paycomet.com/ → Mis productos → configurar productos Y seleccionamos el producto que vayamos a configurar en nuestra tienda Prestashop.
En URL OK (cobro con éxito) indicaremos la Url OK que se muestra en la configuración del producto
En URL KO (error en el cobro) indicaremos la Url KO que se muestra en la configuración del producto

En _tipo de notificación_ Marcamos _Notificación por URL_ o _Notificación por URL y por email_, finalmente en _URL Notificación_ ponemos la Url Notificación que se muestra en la configuración del producto


### Operativa BANKSTORE

Cuando el cliente vaya a pagar el carrito, se le mostrará un check para indicar si quiere almacenar su tarjeta para futuras compras. Si lo chequea y finaliza el pedido la siguiente vez que vaya a pagar un carrito en la tienda se le mostrarán las tarjetas que tenga almacenadas para poder seleccionar la que desee y pagar de forma más rápida.

En cualquier momento el cliente puede eliminar sus tarjetas vinculadas en la tienda en su Area de Usuario->Mis tarjetas y Suscripciones.

SUSCRIPCIONES: Si se ha activado esta opcion en la configuración, a la hora de realizar el pago se le mostrará un check "Desea suscribirse a este producto". Si lo selecciona, deberá indicar la peridicidad del pedido y el numero de Pagos que desea realizar. Pulsando a suscribirse deberá realizar el pago inicial. Cuando se cumpla el plazo de la suscripción automáticamente se generará un nuevo pedido idéntico al definido en la suscripción.

En cualquier momento el usuario podrá Cancelar sus suscripciones desde el Area de Usuario->Mis tarjetas y Suscripciones. En dicho apartado se mostrarán todas las suscripciones activas, donde además podrá ver todos los pedidos realizados durante dicha suscripción. Tendrá una opción "Cancelar suscripcion" para darse de baja en cualquier momento.

Todas las suscripciones mostrarán el estado actual:

- Eliminar Suscripción: Indica que es una suscripción activa y que se puede cancelar en cualquier momento.
- CANCELADA: Cuando ha sido cancelada por el usuario
- FINALIZADA: Cuando se ha cumplido todo el periodo de la suscripción

