  <?php defined("SYSPATH") or die("No direct script access.");/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2013 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
class advanced_search_Controller extends Controller {


  public function index(){
     $items_okay = array();
      $view = new Theme_View("page.html", "collection", "advanced_search");
      $view->content = new View ("advanced_search.html");
      $view->content->offset = 0;
      $view->content->limit = module::get_var("advanced_search","limit");
      $view->content->items = $items_okay;
      $view->content->enable_exif_gps = module::is_active("exif_gps");
      $view->content->enable_tags = module::is_active("tag"); 
      $view->content->groups = ORM::factory("group")
            ->join("groups_users", "groups_users.group_id", "groups.id", "left")
            ->where("groups_users.group_id", "IS NOT", null)
            ->group_by("id")->find_all();  
      print $view;
  }

   public function search() {
    
      $enable_exif_gps = module::is_active("exif_gps");
      $enable_tags = module::is_active("tag");
      $limit = module::get_var("advanced_search","limit");

      $title = Input::instance()->post("title");
      $description = Input::instance()->post("description");
      $tags = Input::instance()->post("tags");
      $login = Input::instance()->post("login");
      $fullname = Input::instance()->post("fullname");
      $without = Input::instance()->post("without");
      $groups = Input::instance()->post("groups");
      $orderby = Input::instance()->post("orderby");
      $type = Input::instance()->post("type");
      $offset = Input::instance()->post("offset");
      $dateby = Input::instance()->post("dateby");
      $datefrom = Input::instance()->post("datefrom");
      $dateto = Input::instance()->post("dateto");


      $form = array("tags" => $tags, "title" => $title, "login" => $login, "fullname" => $fullname,
       "description" => $description, "groups" => $groups, "orderby" => $orderby, "type" => $type,
        "withouttag" => in_array("withouttag", $without), "withoutgps" => in_array("withoutgps", $without),
        "dateby" => $dateby, "datefrom" => $datefrom, "dateto" => $dateto);

      $timestampfrom = strtotime(str_replace('/', '-', $datefrom));
      $timestampto = strtotime(str_replace('/', '-', $dateto));

      /*
        O post manda a informacao da do group no seguinte formato:
        groups
        {
          select_index: o indice do group dentro do select
          group_id: é o id do group correspondente ao index do select
        }
      */
      $values = explode(":", $groups);
      $group_id = $values[1];

      if($group_id > 0){
        $groups_users = db::build()->select()->from("groups_users")->where("group_id", "=", $group_id)->execute();
         
            $group_users_id = array();  
            $i = 0;
            foreach ($groups_users as $group_users) {
              $group_users_id[$i] = $group_users->user_id;
              $i++;
            }
      } 


      $users = ORM::factory("user");
      $items = ORM::factory("item");
      $items2 = ORM::factory("item");

      if($login){
          $users->where("name", "like","%".trim($login)."%");
      }
      if($fullname){
          $users->where("full_name", "like","%".trim($fullname)."%");
      }

      $users = $users->find_all();

      if($users->as_array()){
        $users_id = array(); 
        $i = 0;
        foreach ($users as $user) {
            $users_id[$i] = $user->id;
            $user_array[$user->id] = $user;
            $i++;
        }
      }

      $user_result = array_intersect($group_users_id, $users_id);

      if($title){
        $items->where("title", "like","%".trim($title)."%");
        $items2->where("title", "like","%".trim($title)."%");
      }
      if($description){
        $items->where("description", "like","%".trim($description)."%");
        $items2->where("description", "like","%".trim($description)."%"); 
      }

      if($enable_tags){
        $tags_data = array();
         foreach (explode(",", $tags) as $tag_name) {
            if ($tag_name) {
                $tag = ORM::factory("tag")->where("name", "=", trim($tag_name))->find();
                $tags_data[] = $tag->id;  
                 if(is_null($tag->id)){
                  $tags_data = array();
                  break; 
                }
            }
         }
       
        if($tags_data){
          $total_tags = count($tags_data);
      
          $items->join("items_tags","items.id","items_tags.item_id", "left")
            ->where("items_tags.tag_id","IN",$tags_data)
            ->group_by("items_tags.item_id")
            ->having('count("*")', '>=', $total_tags);

          $items2->join("items_tags","items.id","items_tags.item_id", "left")
            ->where("items_tags.tag_id","IN",$tags_data)
            ->group_by("items_tags.item_id")
            ->having('count("*")', '>=', $total_tags);
        }

        if(in_array("withouttag", $without)){
          $items->join("items_tags", "items.id", "items_tags.item_id", "left")
            ->where("items_tags.item_id", "IS", null);
          $items2->join("items_tags", "items.id", "items_tags.item_id", "left")
            ->where("items_tags.item_id", "IS", null);  
        }
      }

      if($enable_exif_gps){
        if(in_array("withoutgps", $without)){
          $items->join("exif_coordinates", "items.id", "exif_coordinates.item_id", "left")
            ->where("exif_coordinates.item_id", "IS", null);
          $items2->join("exif_coordinates", "items.id", "exif_coordinates.item_id", "left")
            ->where("exif_coordinates.item_id", "IS", null);  
        }
      }

      switch ($type) {
        case "0":
          $items->where("type", "=", "photo");
          $items2->where("type", "=", "photo");
          break;
        case "1":
          $items->where("type", "=", "movie");
          $items2->where("type", "=", "movie");          
          break;
        case "2":
          $items->where("type", "=", "album");
          $items2->where("type", "=", "album");
          break;
        case "3":
          break;
      }

      switch ($dateby) {
        case "0":
          break;
        case "1":
          $items->where("captured", 'BETWEEN', array($timestampfrom, $timestampto));
          $items2->where("captured", 'BETWEEN', array($timestampfrom, $timestampto));
          break;
        case "2":
          $items->where("created", 'BETWEEN', array($timestampfrom, $timestampto));
          $items2->where("created", 'BETWEEN', array($timestampfrom, $timestampto));
          break;
        case "3":
          $items->where("updated", 'BETWEEN', array($timestampfrom, $timestampto));
          $items2->where("updated", 'BETWEEN', array($timestampfrom, $timestampto));
          break;
      }

      switch ($orderby) {
        case "0":
          $items->order_by("owner_id","asc");
          break;
        case "1":
          $items->order_by("title","asc");
          break;
        case "2":
          $items->order_by("captured","asc");
          break;
        case "3":
          $items->order_by("created","asc");
          break;
        case "4":
          $items->order_by("updated","asc");
          break;
      }

      $items->where("owner_id","IN",$user_result);
      $items2->where("owner_id","IN",$user_result);

      $groups_okay = ORM::factory("group"); 
      $groups_okay->join("groups_users", "groups_users.group_id", "groups.id", "left")
            ->where("groups_users.group_id", "IS NOT", null)
            ->group_by("id");
      $groups_okay = $groups_okay->find_all();

      $view = new Theme_View("page.html", "collection", "advanced_search");
      $view->content = new View ("advanced_search.html");
      $view->content->enable_exif_gps = $enable_exif_gps;
      $view->content->enable_tags = $enable_tags;
      $view->content->groups = $groups_okay;
             
      if($login || $fullname || $title || $description || $tags || $without) {
             
        if($users->as_array()){

         $items = $items->find_all($limit,$offset);

         $total = $items2->find_all()->count();
                  
          $items_okay = $items->as_array();

          foreach ($items_okay as $key => $item) {
            if(!access::can("view",$item)){
               unset($items_okay[$key]);
            }
          }

          $view->content->items = $items_okay;
          $view->content->users = $user_array;
          $view->content->form = $form;
          $view->content->offset = $offset;
          $view->content->limit = $limit;
          $view->content->total = $total;
        }  
      }else{
        $view->content->form = $form;
        $items_okay = array();
        message::warning(t("Campos em Branco"));
      }
      print $view;
 }

  public function form_delete($id) {
    $item = model_cache::get("item", $id);
    access::required("view", $item);
    access::required("edit", $item);


    $form = new Forge("#", "","post", array("id" => "g-confirm-delete"));
    $group = $form->group("confirm_delete")->label(t("Confirm Deletion"));

      //pega csrf para enviar no post de exclusao.
     $csrf = access::csrf_token();

    $group->script('delete_submit')->text('

      var delete_submit = function() {
        $("input[name=ponei]").click(function(){
          $.post("'. url::site("advanced_search/delete/{$item->id}?csrf={$csrf}").'", function(data){
            $("#g-dialog").dialog("close");
            $("#btn-search").click();
          });
          return false;
        });
      }
      $(document).ready(delete_submit);
    ');

    $group->submit("ponei")->value(t("Delete"));
    $form->script("")->url(url::abs_file("modules/gallery/js/item_form_delete.js"));

    $v = new View("quick_delete_confirm.html");
    $v->item = $item;
    $v->form = $form;
    print $v;
  }

  public function delete($id) {
    access::verify_csrf();

    $item = model_cache::get("item", $id);
    access::required("view", $item);
    access::required("edit", $item);

    if ($item->is_album()) {
      $msg = t("Deleted album <b>%title</b>", array("title" => html::purify($item->title)));
    } else {
      $msg = t("Deleted photo <b>%title</b>", array("title" => html::purify($item->title)));
    }

    $parent = $item->parent();

    if ($item->is_album()) {
      batch::start();
      $item->delete();
      batch::stop();
    } else {
      $item->delete();
    }

    message::success($msg);
      
    json::reply(array("result" => "success", "reload"));

  }


  /* Edits Functions */

  public function form_edit($id) {
    $item = model_cache::get("item", $id);
    access::required("view", $item);
    access::required("edit", $item);

    switch ($item->type) {
    case "album":
      $form = $this->album_edit_form($item);
      break;

    case "photo":
      $form = $this->photo_edit_form($item);
      break;

    case "movie":
      $form = $this->movie_edit_form($item);
      break;
    }

    // Pass on the source item where this form was generated, so we have an idea where to return to.
    $form->hidden("from_id")->value((int)Input::instance()->get("from_id", 0));

    print $form;
  }


static function album_edit_form($parent) {
    $form = new Forge(
      "advanced_search/album_update/{$parent->id}", "", "post", array("id" => "g-edit-album-form"));
    $form->hidden("from_id")->value($parent->id);
    $group = $form->group("edit_item")->label(t("Edit Album"));

    $group->input("title")->label(t("Title"))->value($parent->title)
        ->error_messages("required", t("You must provide a title"))
      ->error_messages("length", t("Your title is too long"));
    $group->textarea("description")->label(t("Description"))->value($parent->description);
    if ($parent->id != 1) {
      $group->input("name")->label(t("Directory Name"))->value($parent->name)
        ->error_messages("conflict", t("There is already a movie, photo or album with this name"))
        ->error_messages("no_slashes", t("The directory name can't contain a \"/\""))
        ->error_messages("no_trailing_period", t("The directory name can't end in \".\""))
        ->error_messages("required", t("You must provide a directory name"))
        ->error_messages("length", t("Your directory name is too long"));
      $group->input("slug")->label(t("Internet Address"))->value($parent->slug)
        ->error_messages(
          "conflict", t("There is already a movie, photo or album with this internet address"))
        ->error_messages(
          "reserved", t("This address is reserved and can't be used."))
        ->error_messages(
          "not_url_safe",
          t("The internet address should contain only letters, numbers, hyphens and underscores"))
        ->error_messages("required", t("You must provide an internet address"))
        ->error_messages("length", t("Your internet address is too long"));
    } else {
      $group->hidden("name")->value($parent->name);
      $group->hidden("slug")->value($parent->slug);
    }

    $sort_order = $group->group("sort_order", array("id" => "g-album-sort-order"))
      ->label(t("Sort Order"));

    $sort_order->dropdown("column", array("id" => "g-album-sort-column"))
      ->label(t("Sort by"))
      ->options(album::get_sort_order_options())
      ->selected($parent->sort_column);
    $sort_order->dropdown("direction", array("id" => "g-album-sort-direction"))
      ->label(t("Order"))
      ->options(array("ASC" => t("Ascending"),
                      "DESC" => t("Descending")))
      ->selected($parent->sort_order);

    module::event("item_edit_form", $parent, $form);

    $group = $form->group("buttons")->label("");
    $group->hidden("type")->value("album");
    $group->submit("")->value(t("Modify"));
    return $form;
  }

  public function album_update($album_id) {
    access::verify_csrf();
    $album = ORM::factory("item", $album_id);
    access::required("view", $album);
    access::required("edit", $album);

    $form = $this->album_edit_form($album);

    try {
      $valid = $form->validate();
      $album->title = $form->edit_item->title->value;
      $album->description = $form->edit_item->description->value;
      $album->sort_column = $form->edit_item->sort_order->column->value;
      $album->sort_order = $form->edit_item->sort_order->direction->value;
      if (array_key_exists("name", $form->edit_item->inputs)) {
        $album->name = $form->edit_item->inputs["name"]->value;
      }
      $album->slug = $form->edit_item->slug->value;
      $album->validate();
    } catch (ORM_Validation_Exception $e) {
      // Translate ORM validation errors into form error messages
      foreach ($e->validation->errors() as $key => $error) {
        $form->edit_item->inputs[$key]->add_error($error, 1);
      }
      $valid = false;
    }

    if ($valid) {
      $album->save();
      module::event("item_edit_form_completed", $album, $form);

      log::success("content", "Updated album", "<a href=\"albums/$album->id\">view</a>");
      message::success(t("Saved album %album_title",
                         array("album_title" => html::purify($album->title))));

      if ($form->from_id->value == $album->id) {
        // Use the new url; it might have changed.
        json::reply(array("result" => "success", "location" => $album->url()));
      } else {
        // Stay on the same page
        json::reply(array("result" => "success"));
      }
    } else {
      json::reply(array("result" => "error", "html" => (string)$form));
    }
  }

  static function photo_edit_form($photo) {
    $form = new Forge("advanced_search/photo_update/$photo->id", "", "post", array("id" => "g-edit-photo-form"));
    $form->hidden("from_id")->value($photo->id);
    $group = $form->group("edit_item")->label(t("Edit Photo"));
    $group->input("title")->label(t("Title"))->value($photo->title)
      ->error_messages("required", t("You must provide a title"))
      ->error_messages("length", t("Your title is too long"));
    $group->textarea("description")->label(t("Description"))->value($photo->description);
    $group->input("name")->label(t("Filename"))->value($photo->name)
      ->error_messages("conflict", t("There is already a movie, photo or album with this name"))
      ->error_messages("no_slashes", t("The photo name can't contain a \"/\""))
      ->error_messages("no_trailing_period", t("The photo name can't end in \".\""))
      ->error_messages("illegal_data_file_extension", t("You cannot change the photo file extension"))
      ->error_messages("required", t("You must provide a photo file name"))
      ->error_messages("length", t("Your photo file name is too long"));
    $group->input("slug")->label(t("Internet Address"))->value($photo->slug)
      ->error_messages(
        "conflict", t("There is already a movie, photo or album with this internet address"))
      ->error_messages(
        "not_url_safe",
        t("The internet address should contain only letters, numbers, hyphens and underscores"))
      ->error_messages("required", t("You must provide an internet address"))
      ->error_messages("length", t("Your internet address is too long"));

    module::event("item_edit_form", $photo, $form);

    $group = $form->group("buttons")->label("");
    $group->submit("")->value(t("Modify"));
    return $form;
  }

  public function photo_update($photo_id) {
    access::verify_csrf();
    $photo = ORM::factory("item", $photo_id);
    access::required("view", $photo);
    access::required("edit", $photo);

    $form = $this->photo_edit_form($photo);
    try {
      $valid = $form->validate();
      $photo->title = $form->edit_item->title->value;
      $photo->description = $form->edit_item->description->value;
      $photo->slug = $form->edit_item->slug->value;
      $photo->name = $form->edit_item->inputs["name"]->value;
      $photo->validate();
    } catch (ORM_Validation_Exception $e) {
      // Translate ORM validation errors into form error messages
      foreach ($e->validation->errors() as $key => $error) {
        $form->edit_item->inputs[$key]->add_error($error, 1);
      }
      $valid = false;
    }

    if ($valid) {
      $photo->save();
      module::event("item_edit_form_completed", $photo, $form);

      log::success("content", "Updated photo", "<a href=\"{$photo->url()}\">view</a>");
      message::success(
        t("Saved photo %photo_title", array("photo_title" => html::purify($photo->title))));

      if ($form->from_id->value == $photo->id) {
        // Use the new url; it might have changed.
        json::reply(array("result" => "success", "location" => $photo->url()));
      } else {
        // Stay on the same page
        json::reply(array("result" => "success"));
      }
    } else {
      json::reply(array("result" => "error", "html" => (string)$form));
    }
  }

  static function movie_edit_form($movie) {
    $form = new Forge("advanced_search/movie_update/$movie->id", "", "post", array("id" => "g-edit-movie-form"));
    $form->hidden("from_id")->value($movie->id);
    $group = $form->group("edit_item")->label(t("Edit Movie"));
    $group->input("title")->label(t("Title"))->value($movie->title)
      ->error_messages("required", t("You must provide a title batatinha quando nasce...."))
      ->error_messages("length", t("Your title is too long"));
    $group->textarea("description")->label(t("Description"))->value($movie->description);
    $group->input("name")->label(t("Filename"))->value($movie->name)
      ->error_messages(
        "conflict", t("There is already a movie, photo or album with this name"))
      ->error_messages("no_slashes", t("The movie name can't contain a \"/\""))
      ->error_messages("no_trailing_period", t("The movie name can't end in \".\""))
      ->error_messages("illegal_data_file_extension", t("You cannot change the movie file extension"))
      ->error_messages("required", t("You must provide a movie file name"))
      ->error_messages("length", t("Your movie file name is too long"));
    $group->input("slug")->label(t("Internet Address"))->value($movie->slug)
      ->error_messages(
        "conflict", t("There is already a movie, photo or album with this internet address"))
      ->error_messages(
        "not_url_safe",
        t("The internet address should contain only letters, numbers, hyphens and underscores"))
      ->error_messages("required", t("You must provide an internet address"))
      ->error_messages("length", t("Your internet address is too long"));

    module::event("item_edit_form", $movie, $form);

    $group = $form->group("buttons")->label("");
    $group->submit("")->value(t("Modify"));

    return $form;
  }

  public function movie_update($movie_id) {
    access::verify_csrf();
    $movie = ORM::factory("item", $movie_id);
    access::required("view", $movie);
    access::required("edit", $movie);

    $form = $this->movie_edit_form($movie);
    try {
      $valid = $form->validate();
      $movie->title = $form->edit_item->title->value;
      $movie->description = $form->edit_item->description->value;
      $movie->slug = $form->edit_item->slug->value;
      $movie->name = $form->edit_item->inputs["name"]->value;
      $movie->validate();
    } catch (ORM_Validation_Exception $e) {
      // Translate ORM validation errors into form error messages
      foreach ($e->validation->errors() as $key => $error) {
        $form->edit_item->inputs[$key]->add_error($error, 1);
      }
      $valid = false;
    }

    if ($valid) {
      $movie->save();
      module::event("item_edit_form_completed", $movie, $form);

      log::success("content", "Updated movie", "<a href=\"{$movie->url()}\">view</a>");
      message::success(
        t("Saved movie %movie_title", array("movie_title" => html::purify($movie->title))));

      if ($form->from_id->value == $movie->id) {
        // Use the new url; it might have changed.
        json::reply(array("result" => "success", "location" => $movie->url()));
      } else {
        // Stay on the same page
        json::reply(array("result" => "success"));
      }
    } else {
      json::reply(array("result" => "error", "html" => (string) $form));
    }
  }

}