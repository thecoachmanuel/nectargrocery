<?php

namespace App\Http\Controllers\API;

use App\Models\Menu;
use App\Models\Footer;
use App\Models\Currency;
use App\Models\Language;
use App\Models\HomeTheme;
use App\Models\SocialAuth;
use App\Models\SocialLink;
use App\Models\OfferBanner;
use App\Models\VerifyManage;
use App\Models\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Http\Resources\MenuResource;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\FooterResource;
use App\Http\Resources\LanguageResource;
use App\Http\Resources\HomeThemeResource;
use App\Http\Resources\SocialLinkResource;
use App\Repositories\ThemeColorRepository;
use App\Http\Resources\PaymentGatewayResource;

class MasterController extends Controller
{
    /**
     * Get master data for all.
     *
     * @return mixed
     */
    public function index()
    {
        try {
            $generaleSetting = generaleSetting('setting');
        } catch (\Throwable $e) {
            $generaleSetting = null;
        }

        try {
            $paymentGateways = Cache::rememberForever('payment_gateway', function () {
                return PaymentGateway::where('is_active', true)->get();
            });
        } catch (\Throwable $e) {
            $paymentGateways = collect([]);
        }

        try {
            $verifyManage = Cache::rememberForever('verify_manage', function () {
                return VerifyManage::first();
            });
        } catch (\Throwable $e) {
            $verifyManage = null;
        }

        $shopType = $generaleSetting?->shop_type ?? 'multi';

        try {
            $socialLinks = SocialLink::active()->get();
        } catch (\Throwable $e) {
            $socialLinks = collect([]);
        }

        $themeName = request()->cookie('theme_name');
        try {
            if(app()->environment('local') && $themeName){
                $themeColor = ThemeColorRepository::query()->where('theme_name', $themeName)->first();
                $homeTheme = HomeTheme::where('theme_name', $themeName)->first();
            } else {
                $themeColor = ThemeColorRepository::query()->where('is_default', true)->first();
                $homeTheme = HomeTheme::where('is_active', true)->first();
            }
        } catch (\Throwable $e) {
            $themeColor = null;
            $homeTheme = null;
        }

        $themeColors = (object) [
            'primary' => $themeColor ? $themeColor['primary'] : '#51af5B',
            'primary50' => $themeColor ? $themeColor['variant_50'] : '#687387',
            'primary100' => $themeColor ? $themeColor['variant_100'] : '#edeef1',
            'primary200' => $themeColor ? $themeColor['variant_200'] : '#45bc1a',
            'primary300' => $themeColor ? $themeColor['variant_300'] : '#faad14',
            'primary400' => $themeColor ? $themeColor['variant_400'] : '#ef4444',
            'primary500' => $themeColor ? $themeColor['variant_500'] : '#f6f7f9',
            'primary600' => $themeColor ? $themeColor['variant_600'] : '#17181D',
            'primary700' => $themeColor ? $themeColor['variant_700'] : '#B91747',
            'primary800' => $themeColor ? $themeColor['variant_800'] : '#9B1642',
            'primary900' => $themeColor ? $themeColor['variant_900'] : '#84173E',
            'primary950' => $themeColor ? $themeColor['variant_950'] : '#4A071D',
        ];

        try {
            $languages = Language::all();
        } catch (\Throwable $e) {
            $languages = collect([]);
        }

        $socialAuths = collect([]);
        try {
            foreach (SocialAuth::all() as $socialAuth) {
                $socialAuths[$socialAuth->provider] = [
                    'name' => $socialAuth->name,
                    'client_id' => $socialAuth->client_id,
                    'is_active' => (bool) $socialAuth->is_active,
                    'redirect_url' => $socialAuth->redirect,
                ];
            }
        } catch (\Throwable $e) {}

        try {
            $allCurrencies = Cache::rememberForever('currencies', function () {
                return Currency::where('is_active', true)->get();
            });
        } catch (\Throwable $e) {
            $allCurrencies = collect([]);
        }

        try {
            $defaultCurrency = generaleSetting('defaultCurrency');
        } catch (\Throwable $e) {
            $defaultCurrency = null;
        }
        $defaultRate = ($defaultCurrency && (float)$defaultCurrency->rate > 0) ? (float)$defaultCurrency->rate : 1.0;

        $currencies = $allCurrencies->map(function ($currency) use ($defaultRate) {
            $rate = $currency->is_default ? 1.0 : (float)($currency->rate ?? 1.0);
            $rateFromDefault = $defaultRate > 0 ? $rate / $defaultRate : 1.0;

            return [
                'id' => $currency->id,
                'name' => $currency->name,
                'symbol' => $currency->symbol,
                'rate' => (float) round($rate, 4),
                'rate_from_default' => round($rateFromDefault, 4),
                'is_default' => (bool) $currency->is_default,
            ];
        });

        $phoneMinLength = $verifyManage?->phone_min_length > 0 ? $verifyManage?->phone_min_length : 9;
        $phoneMaxLength = $verifyManage?->phone_max_length > 0 ? $verifyManage?->phone_max_length : 16;

        try {
            $menus = Menu::where('is_active', true)->when($shopType != 'multi', function ($query) {
                return $query->where('url', '!=', '/shops');
            })->orderBy('order')->get();
        } catch (\Throwable $e) {
            $menus = collect([]);
        }

        try {
            $footers = Footer::with('items')->orderBy('order')->get();
        } catch (\Throwable $e) {
            $footers = collect([]);
        }

        return $this->json('Master data', [
            'currency' => [
                'id' => $defaultCurrency?->id ?? 1,
                'name' => $defaultCurrency?->name ?? 'USD',
                'symbol' => $generaleSetting?->currency ?? '$',
                'rate' => (float) $defaultRate,
                'position' => $generaleSetting?->currency_position ?? 'prefix',
            ],
            'currencies' => $currencies,
            'app_name' => $generaleSetting?->name ?? config('app.name'),
            'app_locale' => config('app.locale'),
            'cash_on_delivery' => (bool) ($generaleSetting?->cash_on_delivery ?? true),
            'online_payment' => (bool) ($generaleSetting?->online_payment ?? true),
            'show_download_app' => (bool) ($generaleSetting?->show_download_app ?? true),
            'google_playstore_link' => $generaleSetting?->google_playstore_url ?? null,
            'app_store_link' => $generaleSetting?->app_store_url ?? null,
            'payment_gateways' => PaymentGatewayResource::collection($paymentGateways),
            'multi_vendor' => (bool) ($shopType == 'multi' ? true : false),
            'mobile' => $generaleSetting?->footer_phone ?? '+880123456789',
            'address' => $generaleSetting?->address ?? 'Dhaka, Bangladesh',
            'web_show_footer' => (bool) ($generaleSetting?->show_footer ?? true),
            'web_footer_text' => $generaleSetting?->footer_text ?? 'All right reserved by company',
            'web_logo' => $generaleSetting?->logo ?? asset('assets/logo.png'),
            'web_footer_logo' => $generaleSetting?->footerLogo ?? asset('assets/logoWhite.png'),
            'app_logo' => $generaleSetting?->appLogo ?? asset('assets/logo.png'),
            'footer_qr' => $generaleSetting?->footerQr ?? null,
            'social_links' => SocialLinkResource::collection($socialLinks),
            'theme_colors' => $themeColors,
            'pusher_app_key' => config('broadcasting.connections.pusher.key'),
            'pusher_app_cluster' => config('broadcasting.connections.pusher.options.cluster'),
            'app_environment' => config('app.env'),
            'register_otp_verify' => (bool) ($verifyManage?->register_otp ?? false),
            'register_otp_type' => $verifyManage?->register_otp_type ?? 'email',
            'forgot_otp_type' => $verifyManage?->forgot_otp_type ?? 'email',
            'order_place_account_verify' => (bool) ($verifyManage?->order_place_account_verify ?? false),
            'phone_required' => (bool) $verifyManage?->phone_required ?? true,
            'phone_min_length' => (int) $phoneMinLength,
            'phone_max_length' => (int) $phoneMaxLength,
            'languages' => LanguageResource::collection($languages),
            'social_auths' => $socialAuths,
            'menus' => MenuResource::collection($menus),
            'home_themes' => $homeTheme ? HomeThemeResource::make($homeTheme) : null,
            'footers' => FooterResource::collection($footers),
        ]);
    }
}
