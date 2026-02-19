<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use AuthorizesRequests;
    public function display(Request $request)
    {
        try {
            $this->authorize('view_user');
            $allowedSortBy = ['id', 'name', 'role', 'email', 'mobile'];
            $sort_by = $request->input('sort_by', 'id');
            if (!in_array($sort_by, $allowedSortBy, true)) {
                $sort_by = 'id';
            }

            $sort_order = strtolower((string) $request->input('sort_order', 'asc'));
            if (!in_array($sort_order, ['asc', 'desc'], true)) {
                $sort_order = 'asc';
            }
            $search = $request->input('search');
            $query = User::query();
            if ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            }
            // $person = Auth::user()->name;
            // $users = $query->whereNot('name', $person);
            $users = $query->orderBy($sort_by, $sort_order)->paginate(5);
            return view('admin/users/index', ['users' => $users, 'sort_by' => $sort_by, 'sort_order' => $sort_order, 'search' => $search]);
        } catch (AuthorizationException $e) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to perform this action.');
        }
    }

    public function view($id)
    {
        try {
            $this->authorize('view_user');
            $user = User::find($id);
            $permissions = $user->permissions;
            return view('admin/users/view', ['user' => $user, 'permissions' => $permissions]);
        } catch (AuthorizationException $e) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to perform this action.');
        }
    }
    public function create()
    {
        try {
            $this->authorize('add_user');
            $countryCodes = [
                ['code' => '+44', 'name' => 'UK'],
                ['code' => '+91', 'name' => 'India'],
                ['code' => '+93', 'name' => 'Afghanistan'],
                ['code' => '+355', 'name' => 'Albania'],
                ['code' => '+213', 'name' => 'Algeria'],
                ['code' => '+1684', 'name' => 'American Samoa'],
                ['code' => '+376', 'name' => 'Andorra'],
                ['code' => '+244', 'name' => 'Angola'],
                ['code' => '+1264', 'name' => 'Anguilla'],
                ['code' => '+672', 'name' => 'Antarctica'],
                ['code' => '+1268', 'name' => 'Antigua and Barbuda'],
                ['code' => '+54', 'name' => 'Argentina'],
                ['code' => '+374', 'name' => 'Armenia'],
                ['code' => '+297', 'name' => 'Aruba'],
                ['code' => '+61', 'name' => 'Australia'],
                ['code' => '+43', 'name' => 'Austria'],
                ['code' => '+994', 'name' => 'Azerbaijan'],
                ['code' => '+1242', 'name' => 'Bahamas'],
                ['code' => '+973', 'name' => 'Bahrain'],
                ['code' => '+880', 'name' => 'Bangladesh'],
                ['code' => '+1246', 'name' => 'Barbados'],
                ['code' => '+375', 'name' => 'Belarus'],
                ['code' => '+32', 'name' => 'Belgium'],
                ['code' => '+501', 'name' => 'Belize'],
                ['code' => '+229', 'name' => 'Benin'],
                ['code' => '+1441', 'name' => 'Bermuda'],
                ['code' => '+975', 'name' => 'Bhutan'],
                ['code' => '+591', 'name' => 'Bolivia'],
                ['code' => '+387', 'name' => 'Bosnia and Herzegovina'],
                ['code' => '+267', 'name' => 'Botswana'],
                ['code' => '+55', 'name' => 'Brazil'],
                ['code' => '+246', 'name' => 'British Indian Ocean Territory'],
                ['code' => '+673', 'name' => 'Brunei'],
                ['code' => '+359', 'name' => 'Bulgaria'],
                ['code' => '+226', 'name' => 'Burkina Faso'],
                ['code' => '+257', 'name' => 'Burundi'],
                ['code' => '+855', 'name' => 'Cambodia'],
                ['code' => '+237', 'name' => 'Cameroon'],
                ['code' => '+1', 'name' => 'Canada'],
                ['code' => '+238', 'name' => 'Cape Verde'],
                ['code' => '+1345', 'name' => 'Cayman Islands'],
                ['code' => '+236', 'name' => 'Central African Republic'],
                ['code' => '+235', 'name' => 'Chad'],
                ['code' => '+56', 'name' => 'Chile'],
                ['code' => '+86', 'name' => 'China'],
                ['code' => '+61', 'name' => 'Christmas Island'],
                ['code' => '+672', 'name' => 'Cocos (Keeling) Islands'],
                ['code' => '+57', 'name' => 'Colombia'],
                ['code' => '+269', 'name' => 'Comoros'],
                ['code' => '+242', 'name' => 'Congo'],
                ['code' => '+243', 'name' => 'Congo, Democratic Republic of the'],
                ['code' => '+682', 'name' => 'Cook Islands'],
                ['code' => '+506', 'name' => 'Costa Rica'],
                ['code' => '+225', 'name' => 'Côte d\'Ivoire'],
                ['code' => '+385', 'name' => 'Croatia'],
                ['code' => '+53', 'name' => 'Cuba'],
                ['code' => '+357', 'name' => 'Cyprus'],
                ['code' => '+420', 'name' => 'Czech Republic'],
                ['code' => '+45', 'name' => 'Denmark'],
                ['code' => '+253', 'name' => 'Djibouti'],
                ['code' => '+1767', 'name' => 'Dominica'],
                ['code' => '+1-809', 'name' => 'Dominican Republic'],
                ['code' => '+670', 'name' => 'East Timor'],
                ['code' => '+593', 'name' => 'Ecuador'],
                ['code' => '+20', 'name' => 'Egypt'],
                ['code' => '+503', 'name' => 'El Salvador'],
                ['code' => '+240', 'name' => 'Equatorial Guinea'],
                ['code' => '+291', 'name' => 'Eritrea'],
                ['code' => '+372', 'name' => 'Estonia'],
                ['code' => '+251', 'name' => 'Ethiopia'],
                ['code' => '+500', 'name' => 'Falkland Islands'],
                ['code' => '+298', 'name' => 'Faroe Islands'],
                ['code' => '+679', 'name' => 'Fiji'],
                ['code' => '+358', 'name' => 'Finland'],
                ['code' => '+33', 'name' => 'France'],
                ['code' => '+594', 'name' => 'French Guiana'],
                ['code' => '+689', 'name' => 'French Polynesia'],
                ['code' => '+241', 'name' => 'Gabon'],
                ['code' => '+220', 'name' => 'Gambia'],
                ['code' => '+995', 'name' => 'Georgia'],
                ['code' => '+49', 'name' => 'Germany'],
                ['code' => '+233', 'name' => 'Ghana'],
                ['code' => '+350', 'name' => 'Gibraltar'],
                ['code' => '+30', 'name' => 'Greece'],
                ['code' => '+299', 'name' => 'Greenland'],
                ['code' => '+1473', 'name' => 'Grenada'],
                ['code' => '+590', 'name' => 'Guadeloupe'],
                ['code' => '+1671', 'name' => 'Guam'],
                ['code' => '+502', 'name' => 'Guatemala'],
                ['code' => '+44-1481', 'name' => 'Guernsey'],
                ['code' => '+224', 'name' => 'Guinea'],
                ['code' => '+245', 'name' => 'Guinea-Bissau'],
                ['code' => '+592', 'name' => 'Guyana'],
                ['code' => '+509', 'name' => 'Haiti'],
                ['code' => '+504', 'name' => 'Honduras'],
                ['code' => '+852', 'name' => 'Hong Kong'],
                ['code' => '+36', 'name' => 'Hungary'],
                ['code' => '+354', 'name' => 'Iceland'],
                ['code' => '+62', 'name' => 'Indonesia'],
                ['code' => '+98', 'name' => 'Iran'],
                ['code' => '+964', 'name' => 'Iraq'],
                ['code' => '+353', 'name' => 'Ireland'],
                ['code' => '+44-1624', 'name' => 'Isle of Man'],
                ['code' => '+972', 'name' => 'Israel'],
                ['code' => '+39', 'name' => 'Italy'],
                ['code' => '+1876', 'name' => 'Jamaica'],
                ['code' => '+81', 'name' => 'Japan'],
                ['code' => '+44-1534', 'name' => 'Jersey'],
                ['code' => '+962', 'name' => 'Jordan'],
                ['code' => '+7', 'name' => 'Kazakhstan'],
                ['code' => '+254', 'name' => 'Kenya'],
                ['code' => '+686', 'name' => 'Kiribati'],
                ['code' => '+965', 'name' => 'Kuwait'],
                ['code' => '+996', 'name' => 'Kyrgyzstan'],
                ['code' => '+856', 'name' => 'Laos'],
                ['code' => '+371', 'name' => 'Latvia'],
                ['code' => '+961', 'name' => 'Lebanon'],
                ['code' => '+266', 'name' => 'Lesotho'],
                ['code' => '+231', 'name' => 'Liberia'],
                ['code' => '+218', 'name' => 'Libya'],
                ['code' => '+423', 'name' => 'Liechtenstein'],
                ['code' => '+370', 'name' => 'Lithuania'],
                ['code' => '+352', 'name' => 'Luxembourg'],
                ['code' => '+853', 'name' => 'Macau'],
                ['code' => '+389', 'name' => 'Macedonia'],
                ['code' => '+261', 'name' => 'Madagascar'],
                ['code' => '+265', 'name' => 'Malawi'],
                ['code' => '+60', 'name' => 'Malaysia'],
                ['code' => '+960', 'name' => 'Maldives'],
                ['code' => '+223', 'name' => 'Mali'],
                ['code' => '+356', 'name' => 'Malta'],
                ['code' => '+692', 'name' => 'Marshall Islands'],
                ['code' => '+596', 'name' => 'Martinique'],
                ['code' => '+222', 'name' => 'Mauritania'],
                ['code' => '+230', 'name' => 'Mauritius'],
                ['code' => '+262', 'name' => 'Mayotte'],
                ['code' => '+52', 'name' => 'Mexico'],
                ['code' => '+691', 'name' => 'Micronesia'],
                ['code' => '+373', 'name' => 'Moldova'],
                ['code' => '+377', 'name' => 'Monaco'],
                ['code' => '+976', 'name' => 'Mongolia'],
                ['code' => '+382', 'name' => 'Montenegro'],
                ['code' => '+1664', 'name' => 'Montserrat'],
                ['code' => '+212', 'name' => 'Morocco'],
                ['code' => '+258', 'name' => 'Mozambique'],
                ['code' => '+95', 'name' => 'Myanmar'],
                ['code' => '+264', 'name' => 'Namibia'],
                ['code' => '+674', 'name' => 'Nauru'],
                ['code' => '+977', 'name' => 'Nepal'],
                ['code' => '+31', 'name' => 'Netherlands'],
                ['code' => '+599', 'name' => 'Netherlands Antilles'],
                ['code' => '+687', 'name' => 'New Caledonia'],
                ['code' => '+64', 'name' => 'New Zealand'],
                ['code' => '+505', 'name' => 'Nicaragua'],
                ['code' => '+227', 'name' => 'Niger'],
                ['code' => '+234', 'name' => 'Nigeria'],
                ['code' => '+683', 'name' => 'Niue'],
                ['code' => '+672', 'name' => 'Norfolk Island'],
                ['code' => '+850', 'name' => 'North Korea'],
                ['code' => '+1670', 'name' => 'Northern Mariana Islands'],
                ['code' => '+47', 'name' => 'Norway'],
                ['code' => '+968', 'name' => 'Oman'],
                ['code' => '+92', 'name' => 'Pakistan'],
                ['code' => '+680', 'name' => 'Palau'],
                ['code' => '+970', 'name' => 'Palestine'],
                ['code' => '+507', 'name' => 'Panama'],
                ['code' => '+675', 'name' => 'Papua New Guinea'],
                ['code' => '+595', 'name' => 'Paraguay'],
                ['code' => '+51', 'name' => 'Peru'],
                ['code' => '+63', 'name' => 'Philippines'],
                ['code' => '+64', 'name' => 'Pitcairn Islands'],
                ['code' => '+48', 'name' => 'Poland'],
                ['code' => '+351', 'name' => 'Portugal'],
                ['code' => '+1-787', 'name' => 'Puerto Rico'],
                ['code' => '+974', 'name' => 'Qatar'],
                ['code' => '+242', 'name' => 'Republic of the Congo'],
                ['code' => '+262', 'name' => 'Réunion'],
                ['code' => '+40', 'name' => 'Romania'],
                ['code' => '+7', 'name' => 'Russia'],
                ['code' => '+250', 'name' => 'Rwanda'],
                ['code' => '+590', 'name' => 'Saint Barthélemy'],
                ['code' => '+290', 'name' => 'Saint Helena'],
                ['code' => '+1869', 'name' => 'Saint Kitts and Nevis'],
                ['code' => '+1758', 'name' => 'Saint Lucia'],
                ['code' => '+590', 'name' => 'Saint Martin'],
                ['code' => '+508', 'name' => 'Saint Pierre and Miquelon'],
                ['code' => '+1784', 'name' => 'Saint Vincent and the Grenadines'],
                ['code' => '+685', 'name' => 'Samoa'],
                ['code' => '+378', 'name' => 'San Marino'],
                ['code' => '+239', 'name' => 'São Tomé and Príncipe'],
                ['code' => '+966', 'name' => 'Saudi Arabia'],
                ['code' => '+221', 'name' => 'Senegal'],
                ['code' => '+381', 'name' => 'Serbia'],
                ['code' => '+248', 'name' => 'Seychelles'],
                ['code' => '+232', 'name' => 'Sierra Leone'],
                ['code' => '+65', 'name' => 'Singapore'],
                ['code' => '+421', 'name' => 'Slovakia'],
                ['code' => '+386', 'name' => 'Slovenia'],
                ['code' => '+677', 'name' => 'Solomon Islands'],
                ['code' => '+252', 'name' => 'Somalia'],
                ['code' => '+27', 'name' => 'South Africa'],
                ['code' => '+82', 'name' => 'South Korea'],
                ['code' => '+211', 'name' => 'South Sudan'],
                ['code' => '+34', 'name' => 'Spain'],
                ['code' => '+94', 'name' => 'Sri Lanka'],
                ['code' => '+249', 'name' => 'Sudan'],
                ['code' => '+597', 'name' => 'Suriname'],
                ['code' => '+268', 'name' => 'Swaziland'],
                ['code' => '+46', 'name' => 'Sweden'],
                ['code' => '+41', 'name' => 'Switzerland'],
                ['code' => '+963', 'name' => 'Syria'],
                ['code' => '+886', 'name' => 'Taiwan'],
                ['code' => '+992', 'name' => 'Tajikistan'],
                ['code' => '+255', 'name' => 'Tanzania'],
                ['code' => '+66', 'name' => 'Thailand'],
                ['code' => '+670', 'name' => 'Timor-Leste'],
                ['code' => '+228', 'name' => 'Togo'],
                ['code' => '+690', 'name' => 'Tokelau'],
                ['code' => '+676', 'name' => 'Tonga'],
                ['code' => '+1868', 'name' => 'Trinidad and Tobago'],
                ['code' => '+216', 'name' => 'Tunisia'],
                ['code' => '+90', 'name' => 'Turkey'],
                ['code' => '+993', 'name' => 'Turkmenistan'],
                ['code' => '+1649', 'name' => 'Turks and Caicos Islands'],
                ['code' => '+688', 'name' => 'Tuvalu'],
                ['code' => '+256', 'name' => 'Uganda'],
                ['code' => '+380', 'name' => 'Ukraine'],
                ['code' => '+971', 'name' => 'United Arab Emirates'],
                ['code' => '+44', 'name' => 'United Kingdom'],
                ['code' => '+1', 'name' => 'United States'],
                ['code' => '+598', 'name' => 'Uruguay'],
                ['code' => '+998', 'name' => 'Uzbekistan'],
                ['code' => '+678', 'name' => 'Vanuatu'],
                ['code' => '+58', 'name' => 'Venezuela'],
                ['code' => '+84', 'name' => 'Vietnam'],
                ['code' => '+1284', 'name' => 'British Virgin Islands'],
                ['code' => '+1340', 'name' => 'US Virgin Islands'],
                ['code' => '+681', 'name' => 'Wallis and Futuna'],
                ['code' => '+212', 'name' => 'Western Sahara'],
                ['code' => '+967', 'name' => 'Yemen'],
                ['code' => '+260', 'name' => 'Zambia'],
                ['code' => '+263', 'name' => 'Zimbabwe']
            ];

            return view('admin/users/create', compact('countryCodes'));
        } catch (AuthorizationException $e) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to perform this action.');
        }
    }
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        $user = new User();

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'] ?? 'viewer';
        $user->password = bcrypt($validated['password']);
        $user->country = $validated['country'];
        $user->mobile = $validated['mobile'];
        $user->save();

        $role = $user->role;
        if ($role === 'admin') {
            $user->permissions()->sync(Permission::query()->pluck('id')->all());
        } elseif ($role === 'manager') {
            $permissionNames = [
                'view_user',
                'view_group',
                'create_group',
                'edit_group',
                'view_document_type',
                'create_document_type',
                'edit_document_type',
                'view_document_audit',
                'export_document',
                'approve_document',
                'view_document_versions',
                'restore_document_version',
                'share_document',
                'view_recycle_bin',
                'restore_document',
                'force_delete_document',
            ];
            $permissionIds = Permission::query()->whereIn('name', $permissionNames)->pluck('id')->all();
            $user->permissions()->sync($permissionIds);
        } else {
            $permissionNames = [
                'view_document_type',
            ];
            $permissionIds = Permission::query()->whereIn('name', $permissionNames)->pluck('id')->all();
            $user->permissions()->sync($permissionIds);
        }

        if (!$request->filled('role')) {
            $selectedPermissions = $request->input('permission', []);
            $permissionIds = Permission::query()->whereIn('name', $selectedPermissions)->pluck('id')->all();
            $user->permissions()->sync($permissionIds);
        }
        return redirect()->to('users')->with('success', 'User added successfully!');
    }

    public function edit($id)
    {
        try {
            $this->authorize('edit_user');
            $countryCodes = [
                ['code' => '+44', 'name' => 'UK'],
                ['code' => '+91', 'name' => 'India'],
                ['code' => '+93', 'name' => 'Afghanistan'],
                ['code' => '+355', 'name' => 'Albania'],
                ['code' => '+213', 'name' => 'Algeria'],
                ['code' => '+1684', 'name' => 'American Samoa'],
                ['code' => '+376', 'name' => 'Andorra'],
                ['code' => '+244', 'name' => 'Angola'],
                ['code' => '+1264', 'name' => 'Anguilla'],
                ['code' => '+672', 'name' => 'Antarctica'],
                ['code' => '+1268', 'name' => 'Antigua and Barbuda'],
                ['code' => '+54', 'name' => 'Argentina'],
                ['code' => '+374', 'name' => 'Armenia'],
                ['code' => '+297', 'name' => 'Aruba'],
                ['code' => '+61', 'name' => 'Australia'],
                ['code' => '+43', 'name' => 'Austria'],
                ['code' => '+994', 'name' => 'Azerbaijan'],
                ['code' => '+1242', 'name' => 'Bahamas'],
                ['code' => '+973', 'name' => 'Bahrain'],
                ['code' => '+880', 'name' => 'Bangladesh'],
                ['code' => '+1246', 'name' => 'Barbados'],
                ['code' => '+375', 'name' => 'Belarus'],
                ['code' => '+32', 'name' => 'Belgium'],
                ['code' => '+501', 'name' => 'Belize'],
                ['code' => '+229', 'name' => 'Benin'],
                ['code' => '+1441', 'name' => 'Bermuda'],
                ['code' => '+975', 'name' => 'Bhutan'],
                ['code' => '+591', 'name' => 'Bolivia'],
                ['code' => '+387', 'name' => 'Bosnia and Herzegovina'],
                ['code' => '+267', 'name' => 'Botswana'],
                ['code' => '+55', 'name' => 'Brazil'],
                ['code' => '+246', 'name' => 'British Indian Ocean Territory'],
                ['code' => '+673', 'name' => 'Brunei'],
                ['code' => '+359', 'name' => 'Bulgaria'],
                ['code' => '+226', 'name' => 'Burkina Faso'],
                ['code' => '+257', 'name' => 'Burundi'],
                ['code' => '+855', 'name' => 'Cambodia'],
                ['code' => '+237', 'name' => 'Cameroon'],
                ['code' => '+1', 'name' => 'Canada'],
                ['code' => '+238', 'name' => 'Cape Verde'],
                ['code' => '+1345', 'name' => 'Cayman Islands'],
                ['code' => '+236', 'name' => 'Central African Republic'],
                ['code' => '+235', 'name' => 'Chad'],
                ['code' => '+56', 'name' => 'Chile'],
                ['code' => '+86', 'name' => 'China'],
                ['code' => '+61', 'name' => 'Christmas Island'],
                ['code' => '+672', 'name' => 'Cocos (Keeling) Islands'],
                ['code' => '+57', 'name' => 'Colombia'],
                ['code' => '+269', 'name' => 'Comoros'],
                ['code' => '+242', 'name' => 'Congo'],
                ['code' => '+243', 'name' => 'Congo, Democratic Republic of the'],
                ['code' => '+682', 'name' => 'Cook Islands'],
                ['code' => '+506', 'name' => 'Costa Rica'],
                ['code' => '+225', 'name' => 'Côte d\'Ivoire'],
                ['code' => '+385', 'name' => 'Croatia'],
                ['code' => '+53', 'name' => 'Cuba'],
                ['code' => '+357', 'name' => 'Cyprus'],
                ['code' => '+420', 'name' => 'Czech Republic'],
                ['code' => '+45', 'name' => 'Denmark'],
                ['code' => '+253', 'name' => 'Djibouti'],
                ['code' => '+1767', 'name' => 'Dominica'],
                ['code' => '+1-809', 'name' => 'Dominican Republic'],
                ['code' => '+670', 'name' => 'East Timor'],
                ['code' => '+593', 'name' => 'Ecuador'],
                ['code' => '+20', 'name' => 'Egypt'],
                ['code' => '+503', 'name' => 'El Salvador'],
                ['code' => '+240', 'name' => 'Equatorial Guinea'],
                ['code' => '+291', 'name' => 'Eritrea'],
                ['code' => '+372', 'name' => 'Estonia'],
                ['code' => '+251', 'name' => 'Ethiopia'],
                ['code' => '+500', 'name' => 'Falkland Islands'],
                ['code' => '+298', 'name' => 'Faroe Islands'],
                ['code' => '+679', 'name' => 'Fiji'],
                ['code' => '+358', 'name' => 'Finland'],
                ['code' => '+33', 'name' => 'France'],
                ['code' => '+594', 'name' => 'French Guiana'],
                ['code' => '+689', 'name' => 'French Polynesia'],
                ['code' => '+241', 'name' => 'Gabon'],
                ['code' => '+220', 'name' => 'Gambia'],
                ['code' => '+995', 'name' => 'Georgia'],
                ['code' => '+49', 'name' => 'Germany'],
                ['code' => '+233', 'name' => 'Ghana'],
                ['code' => '+350', 'name' => 'Gibraltar'],
                ['code' => '+30', 'name' => 'Greece'],
                ['code' => '+299', 'name' => 'Greenland'],
                ['code' => '+1473', 'name' => 'Grenada'],
                ['code' => '+590', 'name' => 'Guadeloupe'],
                ['code' => '+1671', 'name' => 'Guam'],
                ['code' => '+502', 'name' => 'Guatemala'],
                ['code' => '+44-1481', 'name' => 'Guernsey'],
                ['code' => '+224', 'name' => 'Guinea'],
                ['code' => '+245', 'name' => 'Guinea-Bissau'],
                ['code' => '+592', 'name' => 'Guyana'],
                ['code' => '+509', 'name' => 'Haiti'],
                ['code' => '+504', 'name' => 'Honduras'],
                ['code' => '+852', 'name' => 'Hong Kong'],
                ['code' => '+36', 'name' => 'Hungary'],
                ['code' => '+354', 'name' => 'Iceland'],
                ['code' => '+62', 'name' => 'Indonesia'],
                ['code' => '+98', 'name' => 'Iran'],
                ['code' => '+964', 'name' => 'Iraq'],
                ['code' => '+353', 'name' => 'Ireland'],
                ['code' => '+44-1624', 'name' => 'Isle of Man'],
                ['code' => '+972', 'name' => 'Israel'],
                ['code' => '+39', 'name' => 'Italy'],
                ['code' => '+1876', 'name' => 'Jamaica'],
                ['code' => '+81', 'name' => 'Japan'],
                ['code' => '+44-1534', 'name' => 'Jersey'],
                ['code' => '+962', 'name' => 'Jordan'],
                ['code' => '+7', 'name' => 'Kazakhstan'],
                ['code' => '+254', 'name' => 'Kenya'],
                ['code' => '+686', 'name' => 'Kiribati'],
                ['code' => '+965', 'name' => 'Kuwait'],
                ['code' => '+996', 'name' => 'Kyrgyzstan'],
                ['code' => '+856', 'name' => 'Laos'],
                ['code' => '+371', 'name' => 'Latvia'],
                ['code' => '+961', 'name' => 'Lebanon'],
                ['code' => '+266', 'name' => 'Lesotho'],
                ['code' => '+231', 'name' => 'Liberia'],
                ['code' => '+218', 'name' => 'Libya'],
                ['code' => '+423', 'name' => 'Liechtenstein'],
                ['code' => '+370', 'name' => 'Lithuania'],
                ['code' => '+352', 'name' => 'Luxembourg'],
                ['code' => '+853', 'name' => 'Macau'],
                ['code' => '+389', 'name' => 'Macedonia'],
                ['code' => '+261', 'name' => 'Madagascar'],
                ['code' => '+265', 'name' => 'Malawi'],
                ['code' => '+60', 'name' => 'Malaysia'],
                ['code' => '+960', 'name' => 'Maldives'],
                ['code' => '+223', 'name' => 'Mali'],
                ['code' => '+356', 'name' => 'Malta'],
                ['code' => '+692', 'name' => 'Marshall Islands'],
                ['code' => '+596', 'name' => 'Martinique'],
                ['code' => '+222', 'name' => 'Mauritania'],
                ['code' => '+230', 'name' => 'Mauritius'],
                ['code' => '+262', 'name' => 'Mayotte'],
                ['code' => '+52', 'name' => 'Mexico'],
                ['code' => '+691', 'name' => 'Micronesia'],
                ['code' => '+373', 'name' => 'Moldova'],
                ['code' => '+377', 'name' => 'Monaco'],
                ['code' => '+976', 'name' => 'Mongolia'],
                ['code' => '+382', 'name' => 'Montenegro'],
                ['code' => '+1664', 'name' => 'Montserrat'],
                ['code' => '+212', 'name' => 'Morocco'],
                ['code' => '+258', 'name' => 'Mozambique'],
                ['code' => '+95', 'name' => 'Myanmar'],
                ['code' => '+264', 'name' => 'Namibia'],
                ['code' => '+674', 'name' => 'Nauru'],
                ['code' => '+977', 'name' => 'Nepal'],
                ['code' => '+31', 'name' => 'Netherlands'],
                ['code' => '+599', 'name' => 'Netherlands Antilles'],
                ['code' => '+687', 'name' => 'New Caledonia'],
                ['code' => '+64', 'name' => 'New Zealand'],
                ['code' => '+505', 'name' => 'Nicaragua'],
                ['code' => '+227', 'name' => 'Niger'],
                ['code' => '+234', 'name' => 'Nigeria'],
                ['code' => '+683', 'name' => 'Niue'],
                ['code' => '+672', 'name' => 'Norfolk Island'],
                ['code' => '+850', 'name' => 'North Korea'],
                ['code' => '+1670', 'name' => 'Northern Mariana Islands'],
                ['code' => '+47', 'name' => 'Norway'],
                ['code' => '+968', 'name' => 'Oman'],
                ['code' => '+92', 'name' => 'Pakistan'],
                ['code' => '+680', 'name' => 'Palau'],
                ['code' => '+970', 'name' => 'Palestine'],
                ['code' => '+507', 'name' => 'Panama'],
                ['code' => '+675', 'name' => 'Papua New Guinea'],
                ['code' => '+595', 'name' => 'Paraguay'],
                ['code' => '+51', 'name' => 'Peru'],
                ['code' => '+63', 'name' => 'Philippines'],
                ['code' => '+64', 'name' => 'Pitcairn Islands'],
                ['code' => '+48', 'name' => 'Poland'],
                ['code' => '+351', 'name' => 'Portugal'],
                ['code' => '+1-787', 'name' => 'Puerto Rico'],
                ['code' => '+974', 'name' => 'Qatar'],
                ['code' => '+242', 'name' => 'Republic of the Congo'],
                ['code' => '+262', 'name' => 'Réunion'],
                ['code' => '+40', 'name' => 'Romania'],
                ['code' => '+7', 'name' => 'Russia'],
                ['code' => '+250', 'name' => 'Rwanda'],
                ['code' => '+590', 'name' => 'Saint Barthélemy'],
                ['code' => '+290', 'name' => 'Saint Helena'],
                ['code' => '+1869', 'name' => 'Saint Kitts and Nevis'],
                ['code' => '+1758', 'name' => 'Saint Lucia'],
                ['code' => '+590', 'name' => 'Saint Martin'],
                ['code' => '+508', 'name' => 'Saint Pierre and Miquelon'],
                ['code' => '+1784', 'name' => 'Saint Vincent and the Grenadines'],
                ['code' => '+685', 'name' => 'Samoa'],
                ['code' => '+378', 'name' => 'San Marino'],
                ['code' => '+239', 'name' => 'São Tomé and Príncipe'],
                ['code' => '+966', 'name' => 'Saudi Arabia'],
                ['code' => '+221', 'name' => 'Senegal'],
                ['code' => '+381', 'name' => 'Serbia'],
                ['code' => '+248', 'name' => 'Seychelles'],
                ['code' => '+232', 'name' => 'Sierra Leone'],
                ['code' => '+65', 'name' => 'Singapore'],
                ['code' => '+421', 'name' => 'Slovakia'],
                ['code' => '+386', 'name' => 'Slovenia'],
                ['code' => '+677', 'name' => 'Solomon Islands'],
                ['code' => '+252', 'name' => 'Somalia'],
                ['code' => '+27', 'name' => 'South Africa'],
                ['code' => '+82', 'name' => 'South Korea'],
                ['code' => '+211', 'name' => 'South Sudan'],
                ['code' => '+34', 'name' => 'Spain'],
                ['code' => '+94', 'name' => 'Sri Lanka'],
                ['code' => '+249', 'name' => 'Sudan'],
                ['code' => '+597', 'name' => 'Suriname'],
                ['code' => '+268', 'name' => 'Swaziland'],
                ['code' => '+46', 'name' => 'Sweden'],
                ['code' => '+41', 'name' => 'Switzerland'],
                ['code' => '+963', 'name' => 'Syria'],
                ['code' => '+886', 'name' => 'Taiwan'],
                ['code' => '+992', 'name' => 'Tajikistan'],
                ['code' => '+255', 'name' => 'Tanzania'],
                ['code' => '+66', 'name' => 'Thailand'],
                ['code' => '+670', 'name' => 'Timor-Leste'],
                ['code' => '+228', 'name' => 'Togo'],
                ['code' => '+690', 'name' => 'Tokelau'],
                ['code' => '+676', 'name' => 'Tonga'],
                ['code' => '+1868', 'name' => 'Trinidad and Tobago'],
                ['code' => '+216', 'name' => 'Tunisia'],
                ['code' => '+90', 'name' => 'Turkey'],
                ['code' => '+993', 'name' => 'Turkmenistan'],
                ['code' => '+1649', 'name' => 'Turks and Caicos Islands'],
                ['code' => '+688', 'name' => 'Tuvalu'],
                ['code' => '+256', 'name' => 'Uganda'],
                ['code' => '+380', 'name' => 'Ukraine'],
                ['code' => '+971', 'name' => 'United Arab Emirates'],
                ['code' => '+44', 'name' => 'United Kingdom'],
                ['code' => '+1', 'name' => 'United States'],
                ['code' => '+598', 'name' => 'Uruguay'],
                ['code' => '+998', 'name' => 'Uzbekistan'],
                ['code' => '+678', 'name' => 'Vanuatu'],
                ['code' => '+58', 'name' => 'Venezuela'],
                ['code' => '+84', 'name' => 'Vietnam'],
                ['code' => '+1284', 'name' => 'British Virgin Islands'],
                ['code' => '+1340', 'name' => 'US Virgin Islands'],
                ['code' => '+681', 'name' => 'Wallis and Futuna'],
                ['code' => '+212', 'name' => 'Western Sahara'],
                ['code' => '+967', 'name' => 'Yemen'],
                ['code' => '+260', 'name' => 'Zambia'],
                ['code' => '+263', 'name' => 'Zimbabwe']
            ];
            $user = User::find($id);
            $permissions = $user->permissions->pluck('name')->toArray();
            return view('admin/users/edit', ['user' => $user, 'countryCodes' => $countryCodes, 'permissions' => $permissions]);
        } catch (AuthorizationException $e) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to perform this action.');
        }
    }
    public function update(UpdateUserRequest $request, User $user, $id)
    {
        $user = User::find($id);
        $validated = $request->validated();
        if (!array_key_exists('role', $validated) || $validated['role'] === null) {
            $validated['role'] = $user->role ?? 'viewer';
        }

        $user->update($validated);

        $role = $user->role ?? 'viewer';
        if ($role === 'admin') {
            $user->permissions()->sync(Permission::query()->pluck('id')->all());
        } elseif ($role === 'manager') {
            $permissionNames = [
                'view_user',
                'view_group',
                'create_group',
                'edit_group',
                'view_document_type',
                'create_document_type',
                'edit_document_type',
                'view_document_audit',
                'export_document',
                'approve_document',
                'view_document_versions',
                'restore_document_version',
                'share_document',
                'view_recycle_bin',
                'restore_document',
                'force_delete_document',
            ];
            $permissionIds = Permission::query()->whereIn('name', $permissionNames)->pluck('id')->all();
            $user->permissions()->sync($permissionIds);
        } else {
            $permissionNames = [
                'view_document_type',
            ];
            $permissionIds = Permission::query()->whereIn('name', $permissionNames)->pluck('id')->all();
            $user->permissions()->sync($permissionIds);
        }

        if (!$request->filled('role')) {
            $selectedPermissions = $request->input('permission', []);
            $permissionIds = Permission::query()->whereIn('name', $selectedPermissions)->pluck('id')->all();
            $user->permissions()->sync($permissionIds);
        }
        return redirect()->to('users')->with('success', 'User updated successfully.');
    }
    public function delete(Request $request)
    {
        try {
            $this->authorize('delete_user');
            $userId = $request->input('delete');
            $user = User::find($userId);
            if ($user->delete()) {
                return redirect()->to('users')->with('success', 'User Deleted Successfully.');
            } else {
                return redirect()->to('users')->with('error', 'User Deleted Failed.');
            }
        } catch (AuthorizationException $e) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to perform this action.');
        }
    }
}
