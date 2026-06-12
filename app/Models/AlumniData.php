<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniData extends Model
{
    protected $table = 'alumni_data';

    protected $fillable = [
        'legacy_user_id',
        'alumni_code',
        'name',
        'email',
        'phone',
        'dob',
        'gender',
        'profile_image',
        'linkedin_url',
        'facebook_url',
        'current_company',
        'current_designation',
        'current_city',
        'current_country',
        'course',
        'branch',
        'campus',
        'institute',
        'level_of_study',
        'joining_year',
        'graduation_year',
        'address_line1',
        'address_line2',
        'address_city',
        'address_state',
        'address_country',
        'address_pincode',
        'record_created_at',
        'record_updated_at',
    ];

    protected $casts = [
        'dob'               => 'date',
        'record_created_at' => 'datetime',
        'record_updated_at' => 'datetime',
    ];

    // ── Column definitions ────────────────────────────────────────────────

    /**
     * All exportable columns with human-readable labels.
     */
    public static function exportableColumns(): array
    {
        return [
            'id'                  => '#',
            'legacy_user_id'      => 'Legacy User ID',
            'alumni_code'         => 'Alumni Code',
            'name'                => 'Name',
            'email'               => 'Email',
            'phone'               => 'Phone',
            'dob'                 => 'Date of Birth',
            'gender'              => 'Gender',
            'profile_image'       => 'Profile Image URL',
            'linkedin_url'        => 'LinkedIn URL',
            'facebook_url'        => 'Facebook URL',
            'institute'           => 'Institute',
            'campus'              => 'Campus',
            'course'              => 'Course',
            'branch'              => 'Branch',
            'level_of_study'      => 'Level of Study',
            'joining_year'        => 'Joining Year',
            'graduation_year'     => 'Graduation Year',
            'current_company'     => 'Company',
            'current_designation' => 'Designation',
            'current_city'        => 'Current City',
            'current_country'     => 'Current Country',
            'address_line1'       => 'Address Line 1',
            'address_line2'       => 'Address Line 2',
            'address_city'        => 'Address City',
            'address_state'       => 'Address State',
            'address_country'     => 'Address Country',
            'address_pincode'     => 'Pincode',
            'record_created_at'   => 'Record Created',
            'record_updated_at'   => 'Record Updated',
        ];
    }

    /**
     * Default columns pre-selected in the export modal.
     */
    public static function defaultExportColumns(): array
    {
        return ['name', 'email', 'phone', 'alumni_code', 'course', 'graduation_year', 'current_company', 'current_city', 'current_country'];
    }

    /**
     * Map CSV/Excel column headers → DB columns.
     * Keys must be lowercase-trimmed versions of the header strings.
     */
    public static function csvColumnMap(): array
    {
        return [
            // Identity
            'userid'                              => 'legacy_user_id',
            'user id'                             => 'legacy_user_id',
            'cq: alumni code'                     => 'alumni_code',

            // Personal
            'name'                                => 'name',
            'cq: name'                            => 'name',
            'email address'                       => 'email',
            'cq: registered emails'               => 'email',
            'mobile number'                       => 'phone',
            'cq: phone numbers'                   => 'phone',
            'date of birth'                       => 'dob',
            'cq: dob'                             => 'dob',
            'cq: gender'                          => 'gender',

            // Social
            'profile image url'                   => 'profile_image',
            'linkedin url'                        => 'linkedin_url',
            'cq: linkedin profile link'           => 'linkedin_url',
            'facebook url'                        => 'facebook_url',
            'cq: facebook profile link'           => 'facebook_url',

            // Employment
            'current company'                     => 'current_company',
            'cq: current company'                 => 'current_company',
            'current designation'                 => 'current_designation',
            'cq: current designation'             => 'current_designation',
            'current city'                        => 'current_city',
            'cq: current city'                    => 'current_city',
            'current country'                     => 'current_country',
            'countries'                           => 'current_country',

            // Academic
            'cq: course name'                     => 'course',
            'cq: course'                          => 'course',
            'course/department'                   => 'course',
            'cq: branch name'                     => 'branch',
            'cq: branch'                          => 'branch',
            'branch/designation'                  => 'branch',
            'cq: campus name'                     => 'campus',
            'cq: institute'                       => 'institute',
            'cq: level of study'                  => 'level_of_study',
            'joining year'                        => 'joining_year',
            'cq: joining year'                    => 'joining_year',
            'leaving year'                        => 'graduation_year',
            'cq: graduation year'                 => 'graduation_year',

            // Address
            'cq: communication address line1'     => 'address_line1',
            'cq: communication address line2'     => 'address_line2',
            'cq: communication address city'      => 'address_city',
            'cq: communication address state'     => 'address_state',
            'cq: communication address country'   => 'address_country',
            'cq: communication address pincode'   => 'address_pincode',

            // Timestamps
            'registration date'                   => 'record_created_at',
            'last updated at'                     => 'record_updated_at',
            'cq: last updated at'                 => 'record_updated_at',
        ];
    }
}