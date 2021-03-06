<?php 
Class Mediciones_model extends CI_Model{
	function __construct() {
        parent::__construct();

        /*
         * db_configuracion es la conexion a los datos de configuración de la aplicación, como lo son los sectores, vías,
         * tramos, entre otros.
         * Esta se llama porque en el archivo database.php la variable ['configuracion']['pconnect] esta marcada como false,
         * lo que quiere decir que no se conecta persistentemente sino cuando se le invoca, como en esta ocasión.
         */
        $this->db_configuracion = $this->load->database('configuracion', TRUE);
    }

    /**
     * Elimina registros en base de datos
     * 
     * @param  [string] $tipo   [lo que eliminará]
     * @param  [int] $id        [Id del registro a eliminar]
     * @return [boolean]        [true]
     */ 
    function eliminar($tipo, $id){
        // Según el tipo
        switch ($tipo) {
            case 'medicion_detalle':
                if($this->db->delete('mediciones_detalle', $id)){
                    return true;
                }
            break;
        }
    }

    /**
     * Permite la inserción de datos en la base de datos 
     * 
     * @param  [string] $tipo  Tipo de inserción
     * @param  [array]  $datos Datos que se van a insertar
     * 
     * @return [boolean]        true, false
     */
    function insertar($tipo, $datos)
    {
        switch ($tipo) {
            case "medicion":
                return $this->db->insert('mediciones', $datos);
            break;
        }

        switch ($tipo) {
            case "medicion_detalle":
                return $this->db->insert_batch('mediciones_detalle', $datos);
            break;
        }
    }

    /**
     * Obtiene registros de base de datos
     * y los retorna a las vistas
     * 
     * @param  [string] $tipo Tipo de consulta que va a hacer
     * @param  [int]    $id   Id foráneo para filtrar los datos
     * 
     * @return [array]       Arreglo de datos
     */
    function obtener($tipo, $id = null)
    {
        switch ($tipo) {
            case 'abscisas_limite':
                return $this->db
                    ->select('MAX( d.Abscisa ) Mayor')
                    ->select('MIN( d.Abscisa ) Menor')
                    ->where("Fk_Id_Medicion", $id)
                    ->get("mediciones_detalle d")->row();
            break;

            case 'abscisas_mediciones':
                return $this->db
                    ->select("Abscisa Valor")
                    ->select("Fecha")
                    ->where("Fk_Id_Medicion", $id["id_medicion"])
                    ->or_where("Fk_Id_Medicion", $id["id_medicion_anterior"])
                    ->group_by("Abscisa")
                    ->get("mediciones_detalle")->result();
            break;
            
            case 'fecha_medicion':
                return $this->db
                    ->select("Fecha Valor")
                    ->where($id)
                    ->get("mediciones_detalle")->row();
            break;

            case 'mediciones':
                $this->db
                    ->select(array(
                        "m.Pk_Id",
                        "CONCAT(YEAR(m.Fecha_Inicial), '-', LPAD(MONTH(m.Fecha_Inicial),2,'0') , '-', LPAD(DAY(m.Fecha_Inicial),2,'0')) AS Nombre",
                    ))
                    ->where("m.Fk_Id_Via", $id)
                    ->from("mediciones m")
                    ->order_by("m.Fecha_Inicial", "DESC")
                ;
                
                // return $this->db->get_compiled_select(); // string de la consulta
                return $this->db->get()->result();
            break;

            case 'medicion':
                $this->db
                    ->select(array(
                        'm.Pk_Id',
                        'm.Abscisa_Inicial',
                        "ROUND(m.Abscisa_Inicial/1000, 0) Kilometro_Inicial",
                        'm.Abscisa_Final',
                        "ROUND(m.Abscisa_Final/1000, 0) Kilometro_Final",
                        'm.Fecha_Inicial',
                        'v.Nombre Via',
                        'v.Fk_Id_Sector',
                        's.Codigo Sector',
                        'm.Fk_Id_Via',
                        'CONCAT(u.Nombres, " ", u.Apellidos) Usuario',
                        ))
                    ->from('mediciones m')
                    ->join('configuracion.vias v', 'm.Fk_Id_Via = v.Pk_Id')
                    ->join('configuracion.sectores s', 'v.Fk_Id_Sector = s.Pk_Id')
                    ->join('configuracion.usuarios u', 'm.Fk_Id_Usuario = u.Pk_Id')
                    ->where('m.Pk_Id', $id)
                ;

                // return $this->db->get_compiled_select(); // string de la consulta
                return $this->db->get()->row();
            break;

            case 'medicion_anterior':
                $this->db
                    ->select(array(
                        'm.Pk_Id',
                        'm.Fecha_Inicial',
                        ))
                    ->from('mediciones m')
                    ->where('m.Fk_Id_Via', $id["id_via"])
                    ->where("m.Pk_Id <", $id["id_medicion"])
                    ->order_by('m.Fecha_Inicial', 'DESC')
                    ->limit(1)
                ;

                // return $this->db->get_compiled_select(); // string de la consulta
                return $this->db->get()->row();
            break;

            case 'medicion_detalle':
                $this->db
                    ->select(array(
                        'd.Calificacion',
                        'd.Factor_Externo',
                        'd.Fecha',
                        'd.Fk_Id_Costado',
                        'd.Fk_Id_Medicion',
                        'd.Fk_Id_Tipo_Medicion',
                        'c.Color_R',
                        'c.Color_G',
                        'c.Color_B',
                        ))
                    ->from('mediciones_detalle d')
                    ->join('valores_calificaciones c', 'd.Calificacion = c.Valor')
                    ->where($id)
                ;

                // return $this->db->get_compiled_select(); // string de la consulta
                return $this->db->get()->row();
            break;
            
            case 'resumen':
                if ($id) $this->db->where($id);
                $this->db
                    ->select(array(
                        'm.*',
                        's.Codigo Sector',
                        'v.Nombre Via',
                        ))
                    ->from('mediciones m')
                    ->join('configuracion.vias v', 'm.Fk_Id_Via = v.Pk_Id')
                    ->join('configuracion.sectores s', 'v.Fk_Id_Sector = s.Pk_Id')
                    ->order_by('m.Fecha_Inicial', 'DESC')
                ;
                
                // return $this->db->get_compiled_select(); // string de la consulta
                return $this->db->get()->result();
            break;

            case 'ultima_medicion':
                return $this->db->where("Fk_Id_Via", $id)->order_by("Pk_Id", "DESC")->get("mediciones")->row();
            break;
        }
    }
}
/* Fin del archivo Configuracion_model.php */
/* Ubicación: ./application/models/Configuracion_model.php */