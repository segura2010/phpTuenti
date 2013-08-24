<?php

/*
Todas las funciones de la clase devuelven un Array!
Deberas trabajar con el para mostrar los datos que necesites en tu aplicacion.

Esto es asi para no limitar el uso de la clase y que el usuario pueda trabajar con todos los datos que
Tuenti nos envia a traves de su API.

phpTuenti version 0.0.3
by @alberto__segura
*/

class Tuenti
{
	private $version = '0.7.1';
	private $api = 'http://api.tuenti.com/api/';
	private $loginURL = 'https://secure.tuenti.com/?m=Login&func=do_login';
	private $sid = '';
	private $agent = 'phpTuenti';
	private $email = '';
	private $password = '';

	function __construct($email, $password)
	{
		$this->email = $email;
		$this->password = $password;
	}

	//Antes de usar cualquier otro metodo, debemos usar este para iniciar la sesion
	//y deberá usar el valor devuelto para saber si la conexion se realizó
	//correctamente o no.
	function login()
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_VERBOSE, 1); 
		$cookie = 'pid=c0b53c24;';
		curl_setopt($curl, CURLOPT_COOKIE, $cookie);
		curl_setopt_array($curl, array(
    		CURLOPT_RETURNTRANSFER => 1,
    		CURLOPT_URL => $this->loginURL,
    		CURLOPT_USERAGENT => $this->agent,
    		CURLOPT_POST => 1,
    		CURLOPT_POSTFIELDS => array(
    			'email' => $this->email,
    			'input_password' => $this->password,
    			'timestamp' => '1',
    			'timezone' => '1',
    			'csfr' => '1ce77ff5'
    		)
		));

		$resp = curl_exec($curl); //Realizamos la peticion.
		curl_close($curl); //Cerramos para liberar recursos.

		//Vamos a buscar el SessionID dentro de los headers!
		$inicioSID = strpos($resp, 'sid=');
		$sid = substr($resp, $inicioSID+4);
		$finSID = strpos($sid, ';');
		$sid = substr($sid, 0, $finSID);

		$this->sid = $sid;
		return !($inicioSID == ''); //Devolvemos true o false segun se haya logueado correctamente o no.
	}

	//Puedes seleccionar tu el SessionID que quieras.
	//Si lo seleccionas tu no necesitaras usar el método "login()".
	public function setSID($session)
	{
		$this->sid = $session;
	}

	private function post($data)
	{
		$curl = curl_init();
		curl_setopt_array($curl, array(
    		CURLOPT_RETURNTRANSFER => 1,
    		CURLOPT_URL => $this->api,
    		CURLOPT_USERAGENT => $this->agent,
    		CURLOPT_POST => 1,
    		CURLOPT_POSTFIELDS => $data
		));

		$resp = curl_exec($curl); //Realizamos la peticion.
		curl_close($curl); //Cerramos para liberar recursos.

		return $resp;
	}

	//Devulve un array con toda la informacion del usuario cuyo ID coincide con el parametro "id".
	public function getUsersData($id)
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["getUsersData",{"ids":["'.$id.'"],"fields":["name","surname","avatar","sex","status_post","phone_number","can_add_as_friend","can_send_message","favorite_books_rich","favorite_movies_rich","favorite_music_rich","favorite_quotes_rich","hobbies_rich","website","about_me_title","about_me","birthday","city","province","hometown","last_visit","visits","relationship","looking_for"]}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp[0];
		return $r->users;
	}

	//Devuelve un array con todos los amigos del usuario autentificado con el SID y los datos de cada amigo.
	public function getFriendsData()
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["getFriendsData",{"fields":["name","surname","sex","phone_number","avatar","status_post","birthday","city","province","hometown","last_visit","mvno_subscriber"]}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp[0];
		return $r->friends;
	}

	//Publica el estado indicado por la variable "status" al usuario autentificado con el SID.
    //Se devuelve un array con informacion de la publicacion.
    public function setUserStatus($status)
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["setUserStatus",{"body":"'.$status.'"}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp;
		return $r;
	}

	//Devuelve un array con la informacion de los albums del usuario con el "id" indicado.
    //Si tiene mas de 20 albums, hay que ir pasando paginas.
	public function getUserAlbums($id, $page)
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["getUserAlbums",{"user_id":"'.$id.'","page":"'.$page.'","albums_per_page":"20"}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp[0];
		return $r;
	}

	//Devulve una variable JSON con 20 fotos de la pagina indicada.
    //Ej: $miTuenti->getAlbumPhotos(4444, 0) -> Devulve las primeras 20 fotos del usuario cuyo id es 4444.
    //Ej: $miTuenti->getAlbumPhotos(4444, 1) -> Devulve las siguientes 20 fotos del usuario cuyo id es 4444.
	public function getAlbumPhotos($id, $page)
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["getAlbumPhotos",{"album_id":"tagged","page":"'.$page.'","user_id":"'.$id.'","photos_per_page":"20"}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp[0];
		return $r->album;
	}

	//Devuelve un array con los usuarios etiquetados en la foto.
	public function getPhotoTags($photoID)
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["getPhotoTags",{"photo_id":"'.$photoID.'"}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp[0];
		return $r;
	}

	//Envia el mensaje "message" al usuario con el id indicado. (Mensaje Privado)
    //Devuelve un array que indica si se envio corretamente el mensaje o no.
	public function sendMessage($id, $message)
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["sendMessage",{"body":"'.$message.'","recipient":"'.$id.'","legacy":"false"}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp;
		return $r;
	}

	//Envia el mensaje "message" al usuario con el id indicado en respuesta al hilo de mensajes indicado por "thread". (Mensaje Privado)
    //Devuelve un array que indica si se envio corretamente el mensaje o no.
	public function sendMessageReply($id, $message, $thread)
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["sendMessage",{"body":"'.$message.'","recipient":"'.$id.'","legacy":"false", "thread_key":"'.$thread.'"}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp;
		return $r;
	}

	//Devuelve un array con la informacion de los 20 mensajes de la pagina indicada. BUZON DE ENTRADA
    //Hay que ir pasando la pagina para ver mas mensajes.
	public function getInBox($page)
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["getInBox",{"page":"'.$page.'"}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp[0];
		return $r;
	}

	//Devuelve un array con la informacion de los 20 mensajes de la pagina indicada. BUZON DE SALIDA
    //Hay que ir pasando la pagina para ver mas mensajes.
	public function getSentBox($page)
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["getSentBox",{"page":"'.$page.'"}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp[0];
		return $r;
	}

	//Devuelve un array con la informacion de los 20 mensajes de la pagina indicada. BUZON DE DESCONOCIDOS
    //Hay que ir pasando la pagina para ver mas mensajes.
	public function getSpamBox($page)
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["getSpamBox",{"page":"'.$page.'"}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp[0];
		return $r;
	}

	#Devuelve un array con las notificaciones del usuario autentificado.
	public function getUserNotifications()
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["getUserNotifications",{"max_notifications":20,"types":["new_commented_status","new_friend_requests", "unread_friend_messages","unread_spam_messages","new_profile_wall_posts","new_friend_requests","accepted_friend_requests","new_photo_wall_posts","new_tagged_photos","new_event_invitations","new_group_page_invitations","group_admin_promotions","group_member_promotions","mentions_bare","like_photos","like_posts_bare"]}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp[0];
		return $r;
	}

	#Devuelve un array con los eventos proximos del usuario.
	public function getUpcomingEvents()
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["getUpcomingEvents",{"desired_number":"20","include_friend_birthdays":"true"}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp[0];
		return $r->list;
	}

	#Devulve un array con los resultados de la busqueda de PERSONAS usando la palabra "word"
    #Hay que ir pasando paginas para ir obteniendo mas resultados (20 por pagina)
    public function searchPeople($word, $page)
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["search",{"results_per_page":"20","page":"'.$page.'","category":"people","filters":{"scope":"all"},"string":"'.$word.'"}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp[0];
		return $r;
	}

	#Devulve un array con los resultados de la busqueda de LUGARES usando la palabra "word"
    #Hay que ir pasando paginas para ir obteniendo mas resultados (20 por pagina)
    public function searchPlaces($word, $page)
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["search",{"results_per_page":"20","page":"'.$page.'","category":"places","filters":{"scope":"all"},"string":"'.$word.'"}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp[0];
		return $r;
	}

	#Devulve un array con los resultados de la busqueda de LUGARES usando la palabra "word"
    #Hay que ir pasando paginas para ir obteniendo mas resultados (20 por pagina)
    public function searchPages($word, $page)
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["search",{"results_per_page":"20","page":"'.$page.'","category":"pages","filters":{"scope":"all"},"string":"'.$word.'"}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp[0];
		return $r;
	}

	#Envia una peticion de amistad con el mensaje indicado al usuario con el id indicado.
    #Devulve si se envio correctamente la peticion.
	public function sendFriendRequest($id, $message)
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["sendFriendRequest",{"user_id":"'.$id.'","message":"'.$message.'"}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp;
		return $r;
	}

	#Publica un comentario en el muro de un usuario con el id indicado
    #Devulve si se envio correctamente la peticion.
    public function addPostToProfileWall($id, $message)
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["addPostToProfileWall",{"user_id":"'.$id.'","body":"'.$message.'","legacy":"false"}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp;
		return $r;
	}

	#Publica un comentario en el estado de un usuario. 
	#Se debe indicar tanto el id del usuario al que pertenece el estado como el id del estado a comentar.
    #Devulve si se envio correctamente la peticion.
    public function addPostToStatus($id, $statusID, $message)
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["addCommentToProfileWall",{"user_id":"'.$id.'","body":"'.$message.'", "post_id":"'.$statusID.'", "legacy":"false"}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp;
		return $r;
	}

	#Da a me gusta a un estado. Se debe indicar el id del usuario al que pertenece el estado y el id del estado.
    #Devulve si se envio correctamente la peticion.
    public function likeWallPost($id, $statusID)
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["likeWallPost",{"user_id":"'.$id.'","post_id":"'.$statusID.'"}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp;
		return $r;
	}

	#Elimina un "me gusta" de un estado. Se debe indicar el id del usuario al que pertenece el estado y el id del estado.
    #Devulve si se envio correctamente la peticion.
    public function unlikeWallPost($id, $statusID)
	{
		$data = '{"session_id":"'.$this->sid.'","version":"'.$this->version.'","requests":[["unlikeWallPost",{"user_id":"'.$id.'","post_id":"'.$statusID.'"}]]}';
		$resp = $this->post($data);
		$rp = json_decode($resp);
		$r = $rp;
		return $r;
	}

}

?>
