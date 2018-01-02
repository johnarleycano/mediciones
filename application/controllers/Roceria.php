<?php
defined('BASEPATH') OR exit('El acceso directo a este archivo no está permitido');

/**
 * @author:     John Arley Cano Salinas
 * Fecha:       28 de noviembre de 2017
 * Programa:    Mediciones | Módulo de rocería
 *              Medición, visualización y demás interacciones
 *              en la medición de la rocería
 * Email:       johnarleycano@hotmail.com
 */
class Roceria extends CI_Controller {
	/**
	 * Función constructora de la clase. Se hereda el mismo constructor 
	 * de la clase para evitar sobreescribirlo y de esa manera 
     * conservar el funcionamiento de controlador.
	 */
	function __construct() {
        parent::__construct();
        
        // Carga de modelos
        $this->load->model(array('configuracion_model', 'roceria_model'));
    }

    /**
     * Interfaz inicial de la rocería
     * 
     * @return [void]
     */
	function index()
	{
        $this->data['titulo'] = 'Rocería';
        $this->data['contenido_principal'] = 'roceria/index';
        $this->load->view('core/template', $this->data);
	}

    /**
     * Carga de interfaces vía Ajax
     * 
     * @return [void]
     */
    function cargar_interfaz()
    {
        //Se valida que la peticion venga mediante ajax y no mediante el navegador
        if($this->input->is_ajax_request()){
            $tipo = $this->input->post("tipo");

            switch ($tipo) {
                case "rango_abscisado":
                    $this->data["id_via"] = $this->input->post("id_via");
                    $this->load->view("roceria/rango_abscisado", $this->data);
                break;
            }
        } else {
            // Si la peticion fue hecha mediante navegador, se redirecciona a la pagina de inicio
            redirect('');
        }
    }

    /**
     * Permite la inserción de datos en la base de datos 
     * 
     * @return [void]
     */
    function insertar()
    {
        //Se valida que la peticion venga mediante ajax y no mediante el navegador
        if($this->input->is_ajax_request()){
            // Datos vía POST
            $datos = $this->input->post('datos');
            $tipo = $this->input->post('tipo');

            switch ($tipo) {
                case "medicion_temporal":
                    // Se inserta el registro y log en base de datos
                    if ($this->roceria_model->insertar($tipo, $datos)) {
                        echo $id = $this->db->insert_id();

                        // Se inserta el registro de logs enviando tipo de log y dato adicional si corresponde
                        $this->logs_model->insertar(3, "Medición temporal $id");
                    }
                break;

                case "medicion_detalle_temporal":
                    // Se inserta el registro y log en base de datos
                    if ($this->roceria_model->insertar($tipo, $datos)) {
                        echo $id = $this->db->insert_id();

                        // Se inserta el registro de logs enviando tipo de log y dato adicional si corresponde
                        // $this->logs_model->insertar(3, "Medición temporal $id");
                    }
                break;
            }
        }else{
            //Si la peticion fue hecha mediante navegador, se redirecciona a la pagina de inicio
            redirect('');
        } // if
    }

    function medir()
    {
        // Si no ha iniciado sesión o es un usuario diferente al 1,
        // redirecciona al inicio de sesión
        if(!$this->session->userdata('Pk_Id_Usuario')){
            redirect('sesion/cerrar');
        }

        $this->data['id_medicion_temporal'] = $this->uri->segment(3);
        $this->data['posicion'] = $this->uri->segment(4);
        $this->data['abscisa'] = $this->uri->segment(5);
        $this->data['abscisa_final'] = $this->uri->segment(6);
        $this->data['titulo'] = 'Rocería - Parametrizar';
        $this->data['contenido_principal'] = 'roceria/medir';
        $this->load->view('core/template', $this->data);
    }

    /**
     * Permite la parametrización de la
     * medición de rocería y cunetas 
     * 
     * @return [void]
     */
    function parametrizar()
    {
        // Si no ha iniciado sesión o es un usuario diferente al 1,
        // redirecciona al inicio de sesión
        if(!$this->session->userdata('Pk_Id_Usuario')){
            redirect('sesion/cerrar');
        }
        
        $this->data['titulo'] = 'Rocería - Parametrizar';
        $this->data['contenido_principal'] = 'roceria/parametrizar';
        $this->load->view('core/template', $this->data);
    }

    /**
     * Muestra los resultados de las mediciones
     * para finalizar
     * 
     * @return [void]
     */
	function terminar()
	{
        // Si no ha iniciado sesión o es un usuario diferente al 1,
        // redirecciona al inicio de sesión
        if(!$this->session->userdata('Pk_Id_Usuario')){
            redirect('sesion/cerrar');
        }

        $this->data['titulo'] = 'Rocería - Finalizar';
        $this->data['contenido_principal'] = 'roceria/terminar';
        $this->load->view('core/template', $this->data);
	}
}
/* Fin del archivo Roceria.php */
/* Ubicación: ./application/controllers/Roceria.php */
