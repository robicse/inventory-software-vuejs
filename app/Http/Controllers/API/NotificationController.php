<?php

namespace App\Http\Controllers\API;


use App\Libraries\AllSettingFormat;
use App\Models\Product;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use DB;

class NotificationController extends Controller
{

    public function index()
    {
        $user = Auth::user()->id;
        $notify = Notification::orderBy('id', 'DESC')->get();
        $myNotifications = array();

        foreach ($notify as $key => $noti) {
            $notifyToArray = array_map('intval', explode(',', $noti->notify_to));

            if (!in_array($user, $notifyToArray)) {
                unset($notify[$key]);
            }

            $noti->read_by = array_map('intval', explode(',', $noti->read_by));
        }

        return $notify;
    }

    public function singleView($id)
    {
        $allSet = new AllSettingFormat;

        $booking_id = Notification::find($id);
        $data = Booking::find($booking_id->booking_id);
        $data->notiTitle = Notification::select('event')->where('id', $id)->first()->event;
        $data->service_id = Services::select('title')->where('id', $data->service_id)->first()->title;
        $data->user_id = User::select('email')->where('id', $data->user_id)->first()->email;
        $data->booking_time = $allSet->timeFormat(unserialize($data->booking_time));

        return view('notification.notificationSingleView', ['data' => $data]);
    }

    public function allNoti()
    {
        $data = $this->index();
        return view('notification.allNotification', ['data' => $data]);
    }

    public function reorder()
    {
        $availableStock = Product::join('product_variants', 'product_variants.product_id', '=', 'products.id')
            ->leftJoin('order_items', 'order_items.variant_id', '=', 'product_variants.id')
            ->having('product_variants.re_order', '>=', DB::raw('sum(order_items.quantity)'))
            ->where('product_variants.enabled', 1)
            ->select('products.id as prod_id', 'products.title as prod_title', 'product_variants.attribute_values as attributes',
                DB::raw('sum(quantity) as qty'), 'product_variants.id as variant_id', 'product_variants.re_order as reorder')
            ->groupBy('order_items.variant_id')->get();

        return view('notification.notificationSingleView', ['data' => $availableStock]);
    }

    public function update(Request $request, $id)
    {
        $notifcations = Notification::find($id);

        if (!in_array(Auth::user()->id, explode(',', $notifcations->read_by))) {
            $notifcations->read_by = $notifcations->read_by . ',' . $request->read_by;
            $notifcations->save();

            return response()->json(['success' => true, 'success' => Lang::get('lang.notification_open')]);

        } else {
            return response()->json(['success' => false, 'errors' => 'Error']);
        }
    }

    public function count()
    {
        $count = 0;
        $notifcations = Notification::all();
        $notification_check_time = User::select('notification_check')->where('id', Auth::user()->id)->first();

        foreach ($notifcations as $noti) {
            if (!in_array(Auth::user()->id, explode(',', $noti->read_by)) && in_array(Auth::user()->id, explode(',', $noti->notify_to)) && $notification_check_time->notification_check < $noti->updated_at) {
                $count++;
            }
        }

        return $count;
    }

    public function countUp($id)
    {
        $time = date('Y-m-d H:i:s');
        User::where('id', $id)->update(['notification_check' => $time]);
    }
}
