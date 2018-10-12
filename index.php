<?php

// Incluimos la libreria Slim
include_once 'vendor/autoload.php';

// Guardamos en una variable todos las funciones de Slim
$app = new \Slim\Slim();


// Creamos la conexion a la base de datos

$userdb = 'root';
$passdb = '';
$selectdb = 'angular_api';

$db =  new mysqli('localhost', $userdb, $passdb, $selectdb);

// Copiamos las cabeceras HTTP para permitir el acceso a CORS y deje hacer peticiones AJAX o de cualquier tipo desde el front

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}


// Prueba de funcionamiento, obteniendo una ruta y mostrando algo en ella
$app->get('/pruebaframeworkapi', function() use($app, $db){
    echo "Works Fine!!";
    //comprobar la conexion a BD
    //var_dump($db);
});

// Verificamos que los datos llegan vacios, los guardamos en una variable

function listValidator(){
    if(!isset($data['marca'])){
        $data['marca']=null;
    }
    
    if(!isset($data['descripcion'])){
        $data['descripcion']=null;
    }   

    if(!isset($data['precio'])){
        $data['precio']=null;
    }       

    if(!isset($data['catalogo'])){
        $data['catalogo']=null;
    }   
}


/* 6. METODO PARA SUBIR UNA IMAGEN */
$app->post('/imagemoto', function() use($app, $db){


    // El primer paso es crear la respuesta de error
    $result = array(
        'Estatus'   => 'error',
        'Code'      => 400,
        'Message'   => 'El campo imagen no existe'
    );    

    // comprobar que se hayan subido archivos de tipo file por metodo post, $_FILES['nombrecampoform']

    if(isset($_FILES['uploads'])){
        /*
        1. se crea una variable con una instancia a la libreria piramide uploader
        2. se llama el metodo upload que llama (prefijo imagen, nombrecampoform, carpetadestino, validacionarraydeformatos)
        3. Se llama otro metodo getInfoFile(). Para obtener informacion del archivo
        */
        $piramideUploader = new PiramideUploader();

        $upload = $piramideUploader->upload('image', 'uploads', 'files', array('image/jpeg', 'image/png', 'image/gif', 'application/pdf'));

        $file = $piramideUploader->getInfoFile();

        $file_name = $file['complete_name'];


        // se valida que la carga del archivo sea exitosa

        if(isset($upload) && $upload["uploaded"] == false){

            $result = array(
                'status'    => 'error',
                'code'      => 400,
                'message'   => 'Ocurrio un error '
            );                

        }else{

            $result = array(
                'status'    => 'success',
                'code'      => 200,
                'message'   => 'Se subieron los datos',
                'filename'  => $file_name
            );                  
        }
    }
    

    // Muestra el resultado
    echo json_encode($result);

});




/* 5. METODO PARA ACTUALIZAR UN PRODUCTO */
$app->post('/updatemoto/:id', function($id) use($app, $db){
    $json = $app->request->post('json');
    $data = json_decode($json, true); 


    // Llamamos la funcion que trae las validaciones

    listValidator();     

    // Cambia el sql, se actualiza en base a un ID recibido

    // Hacemos una condicion en caso de recibir una imagen

    $sql = "UPDATE motosnuevas SET ".
    "marca = '{$data['marca']}',".
    "descripcion = '{$data['descripcion']}',".
    "precio = '{$data['precio']}',";

    // si esta la imagen agreguela
    if(isset($data['imagen'])){
        $sql .= "imagen = '{$data['imagen']}',";
    }

    $sql .= "catalogo = '{$data['catalogo']}' WHERE id = {$id};";

    // Usar var_dump para verificar lo que tiene una variable o ver errores

    $query = $db->query($sql);

    if($query){

        $result = array(
            'Estatus'   => 'Success',
            'Code'      => 200,
            'Message'   => 'Registo Actualizado :)!'
        );

    }else{

        $result = array(
            'Estatus'   => 'error',
            'Code'      => 404,
            'Message'   => 'Problema al actualizar registro :('
        );        

    }

    // Mostramos la actualiacion

    echo json_encode($result);
 
 });




/* 4. METODO PARA BORRAR UN PRODUCTO */
$app->get('/deletemoto/:id', function($id) use($app, $db){

    // Cambiamos la consulta query por DELETE
    $sql    = 'DELETE FROM motosnuevas WHERE id ='.$id;
    $query  = $db->query($sql);

    if($query){

        $result = array(
            'Estatus'   => 'Success',
            'Code'      => 200,
            'Message'   => 'Registo Borrado :)!'
        );

    }else{

        $result = array(
            'Estatus'   => 'error',
            'Code'      => 404,
            'Message'   => 'Problema al borrar registro :('
        );        

    }

    // Mostrar el resultado
    echo json_encode($result);
});



/* 3. METODO PARA CONSEGUIR UN PRODUCTO */

// En el metodo, le pasamos el parametro de la id en le url:id
// En la funcion($id) - Le pasamos el parametro id
// Al hacer el query le pasamos el id que ponemos en la url del request
$app->get('/getmoto/:id', function($id) use($app, $db) {
    $sql = 'SELECT * FROM motosnuevas WHERE id ='.$id;
    $query = $db->query($sql);

    // Hacemos un result por defecto por si no muestra el registro
    $result = array(
        'status'    => 'error',
        'code'      => 204,
        'mensaje'   => 'Producto no encontrado'
    );    

    // verificar que el numero de columnas sea igual a 1
    if($query->num_rows == 1){
        // Guarda en una variable solo de a un producto listado
        $producto = $query->fetch_assoc();

        $result = array(
            'status'    => 'success',
            'code'      => 200,
            'mensaje'   => $producto
        );    

    }

    // Mostramos la respuesta
    echo json_encode($result);

});


/* 2. METODO PARA LISTAR LOS PRODUCTOS */

$app->get('/listmotos', function() use($app, $db){
    // ejecutamos la consulta sql para buscar en la tabla los registros mas nuevos
    $sql = 'SELECT * FROM motosnuevas ORDER BY id DESC;';
    // hacer la consulta
    $query = $db->query($sql);

    /* Con metodo Fetch All podemos ver todos los registros en una array
    var_dump($query->fetch_all());*/

    // Es mejor hacer un bucle while para convertir a un array de objetos
    $productos = array();
    while($producto = $query->fetch_assoc()){
        $productos[] = $producto;
    }

    // Mostramos el resultado del array de objetos, esto esta como array pero al mostrarlo en el request se muestra como una array: que es productos y dentro cada uno de los elementos en un objeto json.
    $result = array(
        'status' => 'success',
        'code'   => 200,
        'data'   => $productos
    );

    // MOstramos el resultado del array convertida a una de objetos usables, encode: convierte en json. decode:arrays

    echo json_encode($result);

});


/* 1. METODO PARA GUARDAR PRODUCTO */

$app->post('/savemoto', function() use($app, $db){
   // Guarda en una variable la respuesta al post
   $json = $app->request->post('json');
   // Convierte la respuesta en formato Array
   $data = json_decode($json, true); 

    // Verificamos que los datos llegan vacios

    if(!isset($data['marca'])){
        $data['marca']=null;
    }
    
    if(!isset($data['descripcion'])){
        $data['descripcion']=null;
    }   

    if(!isset($data['precio'])){
        $data['precio']=null;
    }     

    if(!isset($data['imagen'])){
        $data['imagen']=null;
    }         

    if(!isset($data['catalogo'])){
        $data['catalogo']=null;
    }  

   // Luego de obtener los datos, ejecutamos el query para que los inserte en la base de datos MYSQLI

    $query = "INSERT INTO motosnuevas VALUES(NULL,".
    "'{$data['marca']}',".
    "'{$data['descripcion']}',".
    "'{$data['precio']}',".
    "'{$data['imagen']}',".
    "'{$data['catalogo']}'".
    ");";

   // La magia recibimos los datos y los guardamos en la tabla
   $insert = $db->query($query);

    // Si hay error en la subida de archivo, muestra una array
   $result = array(
        'Result' => 'Error',
        'Code'   =>  '404',
        'Mensage' =>  'Error al Guardar los datos',
   );

   if($insert){
        $result = array(
            'result' => 'Success',
            'code'   =>  '200',
            'mensage' =>  'Datos Actualizados',
        );
   }

   echo json_encode($result);

});

// Para que se lanze el API corremos el metodo run()
$app->run();




