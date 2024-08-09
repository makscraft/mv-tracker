<?
class Search extends Plugin
{
   public $request;
   
   private $models = array();
   
   private $results = array();
   
   public function setModels(array $models)
   {
      foreach($models as $model => $params)
         if($this -> registry -> checkModel($model))
         {
            $this -> models[$model]['object'] = new $model();
            
            if(isset($params['search_fields']) && count($params['search_fields']))
               foreach($params['search_fields'] as $field)
                  if($this -> models[$model]['object'] -> getElement($field))
                     $this -> models[$model]['fields'][] = $field;
            
            if(isset($params['conditions']) && count($params['conditions']))
               $this -> models[$model]['conditions'] = $params['conditions'];

            if(isset($params['result_fields']) && count($params['result_fields']))
               $this -> models[$model]['result_fields'] = $params['result_fields'];
            
            if(isset($params['foreign_name']) && count($params['foreign_name']))
               $this -> models[$model]['foreign_name'] = $params['foreign_name'];
         }

      return $this;
   }
   
   public function setSearchRequest($request)
   {
      $this -> request = trim($request);
      
      return $this;
   }
   
   public function makeSearchAndCountResults()
   {
      $found_ids = array();
      
      foreach($this -> models as $model_name => $params)
         foreach($params['fields'] as $field)
         {
            $conditions = isset($params['conditions']) ? $params['conditions'] : array();          
            $conditions["extra->"] = "`".$field."` LIKE '%".$this -> request."%' COLLATE utf8_general_ci";
            $conditions["fields->"] = "id";

            $ids = $params['object'] -> selectColumn($conditions);
            
            if(isset($this -> results[$model_name]))
               $this -> results[$model_name]= array_merge($this -> results[$model_name], $ids);
            else
               $this -> results[$model_name] = $ids;
            
            $this -> results[$model_name] = array_unique($this -> results[$model_name]);
         }
      
      $number = 0;
         
      foreach($this -> results as $model => $ids)
         $number += count($ids);
      
      return $number;
   }
   
   public function liftRelevantResults()
   {
      $buffer = array();
      $request = mb_strtolower($this -> request, "utf-8");
      
      foreach($this -> results as $model => $ids)
      {
         if(!count($ids))
            continue;
         
         $rows = $this -> models[$model]['object'] -> select(array("id->in" => implode(",", $ids)));
         
         foreach($rows as $row)
         {
            $item = array($model, $row);
            
            if(!isset($row["name"]))
            {
               $buffer[] = $item;
               continue;
            }
            
            $name = mb_strtolower($row["name"], "utf-8");
            
            if(mb_strpos($name, $request, 0, "utf-8") !== false)
               array_unshift($buffer, $item);
            else
               $buffer[] = $item;
         }
      }
      
      return $buffer;      
   }
   
   public function display()
   {
      $html = "";
      $current = 1;
      $buffer = $this -> liftRelevantResults();
      $request_lower = mb_strtolower($this -> request, "utf-8");
      $request_extra = mb_strtoupper(mb_substr($request_lower, 0, 1)).mb_strtolower(mb_substr($request_lower, 1));
      
      if(isset($this -> pager))
      {
         $buffer = array_slice($buffer, $this -> pager -> getStart(), $this -> pager -> getLimit());
         $current = $this -> pager -> getStart() + 1;
      }
      
      foreach($buffer as $data)
      {
         $model = $data[0];
         $row = $data[1];
         
         if(method_exists($this -> models[$model]['object'], "createSearchUrl"))
            $url = $this -> models[$model]['object'] -> createSearchUrl($row);
         else
            $url = $this -> root_path.$model."/".$row['id']."/";
           
         $name = "name";
         
         if($model == "documentation")
         	$row[$name] = I18n :: locale("documentation").": ".$row[$name];
         else if($model == "projects")
         	$row[$name] = I18n :: locale("project").": ".$row[$name];
         else if($model == "journal")
         {
         	$task_name = $this -> models[$model]['object'] -> getEnumTitle("task", $row["task"]);
         	$row[$name] = $task_name;
         }
         else
         {
            if(isset($this -> models[$model]['result_fields']['name']))
               $name = $this -> models[$model]['result_fields']['name'];
         }
                  
         $content = isset($this -> models[$model]['result_fields']['content']) ? $this -> models[$model]['result_fields']['content'] : "content";
         $name = $this -> markRequestString($this -> request, $row[$name]);
         
         $description = trim($row[$content]);
         $description = str_replace(array("</li>", "</p>", "</h2>"), array("</li> ", "</p> ", "</h2> "), $description);
         $description = strip_tags($description);

         $description_lower = mb_strtolower($description, "utf-8");
         $found = mb_strpos($description_lower, $request_lower, 0, "utf-8");
         $length = 430;
               
         if($found !== false)
         {
            $start = ($found < 70) ? 0 : $found - 70;
            $long = ((mb_strlen($description, "utf-8") - $start) > $length);
            $overflow = (mb_strlen($description, "utf-8") > $length);
            
            if($overflow)
            {
               $description = mb_substr($description, $start, $length, "utf-8");
               
               if($start)
                  $description = "... ".preg_replace("/^[^\s]*/", "", $description);
            }
            
            if($long)
               $description = preg_replace("/[^\s]*$/", "", $description)." ...";
         }
         else
            $description = Service :: cutText($description, $length, "...");
         
         $description = $this -> markRequestString($this -> request, $description);
         $css = ($description == "" || $description == "&nbsp;") ? " no-content" : "";
                  
         $html .= "<p class=\"search-name".$css."\"><span class=\"number\">".$current.".</span>";
         $html .= "<a target=\"_blank\" href=\"".$url."\">".$name."</a></p>\n";
         $html .= $this -> addProjectName($data);
         
         if($description != "" && $description != "&nbsp;")
         	$html .= "<p class=\"search-decription\">".$description."</p>\n";
         
         $current ++;
      }
         
      return $html;
   }
   
   private function addProjectName($data)
   {
	   if(isset($data[0]) && $data[0] == "tasks" && isset($data[1]["project"]) && $data[1]["project"])
		  if($project = $this -> selectOne(array("table->" => "projects", "id" => $data[1]["project"])))
		  	 return "<p class=\"search-project\">".$project["name"]."</p>\n";
   }
   
   public function markRequestString($request, $string)
   {
      $request_lower = mb_strtolower($request, "utf-8");
      $request_upper = mb_strtoupper($request, "utf-8");
      $request_extra = mb_strtoupper(mb_substr($request_lower, 0, 1)).mb_strtolower(mb_substr($request_lower, 1));
      
      $string = str_replace($request_lower, "<span class=\"search-found\">".$request_lower."</span>", $string);
      $string = str_replace($request_extra, "<span class=\"search-found\">".$request_extra."</span>", $string);
      $string = str_replace($request_upper, "<span class=\"search-found\">".$request_upper."</span>", $string);
      
      if($request != $request_lower && $request != $request_extra && $request != $request_upper)
         $string = str_replace($request, "<span class=\"search-found\">".$request."</span>", $string);
      
      return $string;
   }
}
?>