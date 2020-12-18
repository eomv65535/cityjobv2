<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function index() {
        $enviadores=Chat::join("users",'users.id','chats.quien_envia')
        ->where('chats.quien_recibe','=',auth()->id())
        ->groupBy('chats.quien_envia')
        ->orderByDesc('chats.created_at')->get();
        $primer_enviador=$enviadores[0]->user->id;
        return Inertia::render("Chat/Index", [
            "quien_me_escribe"=>$enviadores,
            "chat" => Chat::join("users",'users.id','=','chats.quien_envia')
                ->where(function ($query, $primer_enviador) {
                    $query->where('chats.quien_recibe','=',auth()->id())
                    ->and('chats.quien_envia',$primer_enviador);
                })
                ,
        ]);

    }

    public function chat_ultimos($cuantos){

        $mensajes= Chat::join("users",'users.id','=','chats.quien_envia')
        ->select('users.name','users.profile_photo_path','users.enlinea','mensaje','chats.created_at')
        ->where('chats.quien_recibe','=',auth()->id())
        ->orderByDesc('chats.created_at')
        ->limit($cuantos)->get();
        return [
            "totalsms" => count($mensajes),
            "mensajes" => $mensajes,

        ];
    }



    public function chatuno(){
        return Inertia::render("Chat/Uno", [
            "chat" => Chat::join("users",'users.id','=','chats.quien_envia')
                ->where(function ($query, $primer_enviador) {
                    $query->where('chats.quien_recibe','=',auth()->id())
                    ->and('chats.quien_envia',request()->only("quien_envia"));
                })
                ,
        ]);
    }

    public function ultimos() {

        return Inertia::render("Chat/Ultimos", [
            "chat" => Chat::join("users",'users.id','=','chats.quien_envia')
            ->where('chats.quien_recibe','=',auth()->id())
            ->orderBy('chats.quien_envia')
            ->orderByDesc('chats.created_at')
            ->limit(5)
            ,
        ]);
    }


    public function create() {
        return Inertia::render("Chat/Create");
    }

    public function store() {
        Chat::create(
            $this->validate(request(), [
                "quien_envia" => "required",
                "quien_recibe" => "required",
                "mensaje" => "required|max:1000",
            ])
        );

        return redirect()->route('chat.index')->with('success', '¡Mensaje enviado!');
    }

}
