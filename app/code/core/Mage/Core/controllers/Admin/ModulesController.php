<?php

class ModulesController extends Ecom_Core_Controller_Zend_Admin_Action
{
     
    function listAction() {
        $blocks = "({'totalRecords':'8',
                    'modules':[{'name':'Module1','module_id':'1','descr':'The layout manager will automatically create'},
                        {'name':'Module2','module_id':'2','descr':'The layout manager will automatically create'},
                        {'name':'Module3','module_id':'3','descr':'The layout manager will automatically create'},
                        {'name':'Module4','module_id':'4','descr':'The layout manager will automatically create'},
                        {'name':'Module5','module_id':'5','descr':'The layout manager will automatically create'},
                        {'name':'Module6','module_id':'6','descr':'The layout manager will automatically create'},
                        {'name':'Module7','module_id':'7','descr':'The layout manager will automatically create'},
                        {'name':'Module8','module_id':'8','descr':'The layout manager will automatically create'}
                    ]})";
        $this->getResponse()->setBody($blocks);
     }
     
    function getImagesAction() {
        $images = '{"images":[{"name":"dance_fever.jpg","size":2067,"lastmod":1171839750000,"url":"images\/thumbs\/dance_fever.jpg"},{"name":"gangster_zack.jpg","size":2115,"lastmod":1171839750000,"url":"images\/thumbs\/gangster_zack.jpg"},{"name":"kids_hug.jpg","size":2477,"lastmod":1171839750000,"url":"images\/thumbs\/kids_hug.jpg"},{"name":"kids_hug2.jpg","size":2476,"lastmod":1171839750000,"url":"images\/thumbs\/kids_hug2.jpg"},{"name":"sara_pink.jpg","size":2154,"lastmod":1171839750000,"url":"images\/thumbs\/sara_pink.jpg"},{"name":"sara_pumpkin.jpg","size":2588,"lastmod":1171839750000,"url":"images\/thumbs\/sara_pumpkin.jpg"},{"name":"sara_smile.jpg","size":2410,"lastmod":1171839750000,"url":"images\/thumbs\/sara_smile.jpg"},{"name":"up_to_something.jpg","size":2120,"lastmod":1171839750000,"url":"images\/thumbs\/up_to_something.jpg"},{"name":"zack.jpg","size":2901,"lastmod":1171839750000,"url":"images\/thumbs\/zack.jpg"},{"name":"zack_dress.jpg","size":2645,"lastmod":1171839750000,"url":"images\/thumbs\/zack_dress.jpg"},{"name":"zack_hat.jpg","size":2323,"lastmod":1171839750000,"url":"images\/thumbs\/zack_hat.jpg"},{"name":"zack_sink.jpg","size":2303,"lastmod":1171839750000,"url":"images\/thumbs\/zack_sink.jpg"},{"name":"zacks_grill.jpg","size":2825,"lastmod":1171839750000,"url":"images\/thumbs\/zacks_grill.jpg"}]}';
        $this->getResponse()->setBody($images);        
    }
    
}