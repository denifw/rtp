<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute harus bisa diterima.',
    'active_url' => ':attribute bukan URL yang valid.',
    'after' => ':attribute harus tanggal setelah :date.',
    'after_or_equal' => ':attribute harus tanggal yang sama atau lebih besar dari :date.',
    'alpha' => ':attribute hanya boleh berisi huruf.',
    'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, tanda penghubung dan garis bawah.',
    'alpha_num' => ':attribute hanya boleh berisi huruf dan angka.',
    'array' => ':attribute Harus himpunan.',
    'before' => ':attribute harus tanggal sebelum :date.',
    'before_or_equal' => ':attribute harus tanggal sebelum atau sama dengan :date.',
    'between' => [
        'numeric' => ':attribute harus diantara :min dan :max.',
        'file' => ':attribute harus diantara :min dan :max kilobytes.',
        'string' => ':attribute harus diantara :min dan :max karakter.',
        'array' => ':attribute harus diantara :min dan :max item.',
    ],
    'boolean' => ':attribute harus benar atau salah.',
    'confirmed' => ':attribute konfirmasi tidak sesuai.',
    'date' => ':attribute bukan tanggal yang valid.',
    'date_equals' => ':attribute harus tanggal yang sama dengan :date.',
    'date_format' => ':attribute tidak sesuai dengan format :format.',
    'different' => ':attribute dan :other harus berbeda.',
    'digits' => ':attribute harus :digits digit.',
    'digits_between' => ':attribute harus diantara :min dan :max digit.',
    'dimensions' => ':attribute memiliki dimensi gambar yang tidak valid.',
    'distinct' => ':attribute memiliki duplikat nilai.',
    'email' => ':attribute harus alaman email yang valid.',
    'exists' => ':attribute yang di pilih tidak valid.',
    'file' => ':attribute harus file.',
    'filled' => ':attribute harus memiliki nilai.',
    'gt' => [
        'numeric' => ':attribute harus lebih besar dari :value.',
        'file' => ':attribute harus lebih dari :value kilobytes.',
        'string' => ':attribute harus lebih dari :value karakter.',
        'array' => ':attribute harus lebih dari :value item.',
    ],
    'gte' => [
        'numeric' => ':attribute harus lebih besar atau sama dengan :value.',
        'file' => ':attribute harus lebih atau sama dengan :value kilobytes.',
        'string' => ':attribute harus lebih atau sama dengan :value karakter.',
        'array' => ':attribute harus memiliki :value item atau lebih.',
    ],
    'image' => ':attribute harus sebuah gambar.',
    'in' => ':attribute yang dipilih tidak sesuai.',
    'in_array' => ':attribute tidak ada di dalam :other.',
    'integer' => ':attribute harus bilangan bulat.',
    'ip' => ':attribute harus alamat IP yang valid.',
    'ipv4' => ':attribute harus alamat IPv4 yang valid.',
    'ipv6' => ':attribute harus alamat IPv6 yang valid.',
    'json' => ':attribute harus sebuah JSON string.',
    'lt' => [
        'numeric' => ':attribute harus kurang dari :value.',
        'file' => ':attribute harus kurang dari :value kilobytes.',
        'string' => ':attribute harus kurang dari :value karakter.',
        'array' => ':attribute harus kurang dari :value item.',
    ],
    'lte' => [
        'numeric' => ':attribute harus kurang atau sama dengan :value.',
        'file' => ':attribute harus sama atau kurang dari :value kilobytes.',
        'string' => ':attribute harus sama atau kurang dari :value karakter.',
        'array' => ':attribute tidak lebih dari :value item.',
    ],
    'max' => [
        'numeric' => ':attribute tidak boleh lebih besar dari :max.',
        'file' => ':attribute tidak boleh lebih dari :max kilobytes.',
        'string' => ':attribute tidak boleh lebih dari :max karakter.',
        'array' => ':attribute tidak boleh lebih dari :max item.',
    ],
    'mimes' => ':attribute harus sebuah file dengan tipe: :values.',
    'mimetypes' => ':attribute harus sebuah file dengan tipe: :values.',
    'min' => [
        'numeric' => ':attribute setidaknya harus :min.',
        'file' => ':attribute setidaknya harus :min kilobytes.',
        'string' => ':attribute stidaknya harus :min karakter.',
        'array' => ':attribute setidaknya harus :min item.',
    ],
    'not_in' => ':attribute yang anda pilih tidak valid.',
    'not_regex' => 'Format :attribute tidak valid.',
    'numeric' => ':attribute harus bilangan numerik.',
    'present' => ':attribute harus ditampilkan.',
    'regex' => 'Format :attribute tidak valid.',
    'required' => ':attribute harus diisi.',
    'required_if' => ':attribute harus diisi kalau :other ialah :value.',
    'required_unless' => ':attribute harus diisi kalau :other ada di dalam :values.',
    'required_with' => ':attribute harus diisi ketika :values disajikan.',
    'required_with_all' => ':attribute harus diisi ketika :values disajikan.',
    'required_without' => ':attribute harus diisi ketika :values tidak ditampilkan.',
    'required_without_all' => ':attribute harus diisi ketika :values tidak ditampilkan.',
    'same' => ':attribute dan :other harus sesuai.',
    'size' => [
        'numeric' => ':attribute harus :size.',
        'file' => ':attribute harus :size kilobytes.',
        'string' => ':attribute harus :size karakter.',
        'array' => ':attribute harus berisi :size item.',
    ],
    'starts_with' => ':attribute harus dimulai dengan salah satu dari: :values',
    'string' => ':attribute harus string.',
    'timezone' => ':attribute harus zona yang valid.',
    'unique' => ':attribute sudah digunakan.',
    'snPrefix' => 'Prefix :attribute tidak valid.',
    'uploaded' => ':attribute gagal di unggah.',
    'url' => 'Format :attribute tidak valid.',
    'uuid' => ':attribute harus UUID yang valid.',
    'alfanumber' => ':attribute tidak boleh mengaduk karakter khusus.',
    'noSerialNumberPrefixData' => 'Tidak ada prefix  serial number ditemukan.',
    'invalidSerialNumberPrefix' => 'Prefix :attribute tidak valid.',
    'invalidCreatingBundle' => 'Jumlah yang tersisa untuk Bundling ialah 0.',
    'invalidLotNumber' => ':attribute tidak valid.',
    'invalidSerialNumber' => ':attribute tidak valid.',
    'snReceiveStoring' => ':attribute harus sesuai dengan SN pada saat penerimaan barang.',
    'currentPassword' => ':attribute tidak sesuai dengan data.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'jdl_first_cp_id' => 'Main Driver'
    ],

];
