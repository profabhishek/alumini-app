<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniData extends Model
{
    protected $table = 'alumni_data';

    protected $fillable = [
        'legacy_user_id',
        'alumni_code',
        'user_type',
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
        'registration_date',
    ];

    protected $casts = [
        'dob'               => 'date',
        'record_created_at' => 'datetime',
        'record_updated_at' => 'datetime',
        'registration_date' => 'datetime',
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
            'user_type'           => 'User Type',
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
            'registration_date'   => 'Registration Date',
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
            // ── Identity ────────────────────────────────────────────────────
            'userid'                              => 'legacy_user_id',
            'user id'                             => 'legacy_user_id',
            'legacy user id'                      => 'legacy_user_id',
            'legacy_user_id'                      => 'legacy_user_id',
            'cq: alumni code'                     => 'alumni_code',
            'alumni code'                         => 'alumni_code',
            'alumni_code'                         => 'alumni_code',
            'usertype'                            => 'user_type',
            'user type'                           => 'user_type',
            'user_type'                           => 'user_type',
            'type'                                => 'user_type',

            // ── Personal ────────────────────────────────────────────────────
            'name'                                => 'name',
            'full name'                           => 'name',
            'full_name'                           => 'name',
            'cq: name'                            => 'name',
            'email'                               => 'email',
            'email address'                       => 'email',
            'emailaddress'                        => 'email',
            'cq: registered emails'               => 'email',
            'registered email'                    => 'email',
            'phone'                               => 'phone',
            'mobile'                              => 'phone',
            'mobile number'                       => 'phone',
            'phone number'                        => 'phone',
            'cq: phone numbers'                   => 'phone',
            'contact'                             => 'phone',
            'date of birth'                       => 'dob',
            'dob'                                 => 'dob',
            'birth date'                          => 'dob',
            'birthdate'                           => 'dob',
            'cq: dob'                             => 'dob',
            'gender'                              => 'gender',
            'cq: gender'                          => 'gender',
            'sex'                                 => 'gender',

            // ── Social / Profile ────────────────────────────────────────────
            'profile image url'                   => 'profile_image',
            'profile image'                       => 'profile_image',
            'profile_image'                       => 'profile_image',
            'photo'                               => 'profile_image',
            'avatar'                              => 'profile_image',
            'linkedin url'                        => 'linkedin_url',
            'linkedin'                            => 'linkedin_url',
            'linkedin_url'                        => 'linkedin_url',
            'cq: linkedin profile link'           => 'linkedin_url',
            'linkedin profile link'               => 'linkedin_url',
            'facebook url'                        => 'facebook_url',
            'facebook'                            => 'facebook_url',
            'facebook_url'                        => 'facebook_url',
            'cq: facebook profile link'           => 'facebook_url',
            'facebook profile link'               => 'facebook_url',

            // ── Employment ──────────────────────────────────────────────────
            'current company'                     => 'current_company',
            'company'                             => 'current_company',
            'current_company'                     => 'current_company',
            'employer'                            => 'current_company',
            'organization'                        => 'current_company',
            'cq: current company'                 => 'current_company',
            'current designation'                 => 'current_designation',
            'designation'                         => 'current_designation',
            'current_designation'                 => 'current_designation',
            'job title'                           => 'current_designation',
            'position'                            => 'current_designation',
            'cq: current designation'             => 'current_designation',
            'current city'                        => 'current_city',
            'city'                                => 'current_city',
            'current_city'                        => 'current_city',
            'cq: current city'                    => 'current_city',
            'current country'                     => 'current_country',
            'country'                             => 'current_country',
            'current_country'                     => 'current_country',
            'countries'                           => 'current_country',
            'cq: current country'                 => 'current_country',

            // ── Academic ────────────────────────────────────────────────────
            'course'                              => 'course',
            'cq: course name'                     => 'course',
            'cq: course'                          => 'course',
            'course name'                         => 'course',
            'course/department'                   => 'course',
            'programme'                           => 'course',
            'program'                             => 'course',
            'degree'                              => 'course',
            'branch'                              => 'branch',
            'cq: branch name'                     => 'branch',
            'cq: branch'                          => 'branch',
            'branch name'                         => 'branch',
            'branch/designation'                  => 'branch',
            'specialization'                      => 'branch',
            'department'                          => 'branch',
            'campus'                              => 'campus',
            'cq: campus name'                     => 'campus',
            'campus name'                         => 'campus',
            'institute'                           => 'institute',
            'institution'                         => 'institute',
            'college'                             => 'institute',
            'university'                          => 'institute',
            'school'                              => 'institute',
            'cq: institute'                       => 'institute',
            'level of study'                      => 'level_of_study',
            'level_of_study'                      => 'level_of_study',
            'cq: level of study'                  => 'level_of_study',
            'study level'                         => 'level_of_study',
            'joining year'                        => 'joining_year',
            'joining_year'                        => 'joining_year',
            'cq: joining year'                    => 'joining_year',
            'admission year'                      => 'joining_year',
            'start year'                          => 'joining_year',
            'year of joining'                     => 'joining_year',
            'graduation year'                     => 'graduation_year',
            'graduation_year'                     => 'graduation_year',
            'leaving year'                        => 'graduation_year',
            'passout year'                        => 'graduation_year',
            'passing year'                        => 'graduation_year',
            'year of graduation'                  => 'graduation_year',
            'cq: graduation year'                 => 'graduation_year',

            // ── Address ──────────────────────────────────────────────────────
            'cq: communication address line1'     => 'address_line1',
            'address line 1'                      => 'address_line1',
            'address line1'                       => 'address_line1',
            'address1'                            => 'address_line1',
            'cq: communication address line2'     => 'address_line2',
            'address line 2'                      => 'address_line2',
            'address line2'                       => 'address_line2',
            'address2'                            => 'address_line2',
            'cq: communication address city'      => 'address_city',
            'address city'                        => 'address_city',
            'address_city'                        => 'address_city',
            'cq: communication address state'     => 'address_state',
            'address state'                       => 'address_state',
            'address_state'                       => 'address_state',
            'state'                               => 'address_state',
            'cq: communication address country'   => 'address_country',
            'address country'                     => 'address_country',
            'address_country'                     => 'address_country',
            'cq: communication address pincode'   => 'address_pincode',
            'pincode'                             => 'address_pincode',
            'pin code'                            => 'address_pincode',
            'zip'                                 => 'address_pincode',
            'zip code'                            => 'address_pincode',
            'postal code'                         => 'address_pincode',

            // ── Timestamps ──────────────────────────────────────────────────
            'registration date'                   => 'registration_date',
            'registered on'                       => 'registration_date',
            'registered at'                       => 'registration_date',
            'record created at'                   => 'record_created_at',
            'created at'                          => 'record_created_at',
            'last updated at'                     => 'record_updated_at',
            'cq: last updated at'                 => 'record_updated_at',
            'updated at'                          => 'record_updated_at',
            'last updated'                        => 'record_updated_at',
        ];
    }
}