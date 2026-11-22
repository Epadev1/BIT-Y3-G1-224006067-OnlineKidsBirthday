<?php
declare(strict_types=1);

final class VendorController
{
    public static function index(): void
    {
        $search = query_string('q');
        $service = query_string('service');
        if (!in_array($service, Vendor::SERVICE_TYPES, true)) {
            $service = '';
        }
        render('vendors/index', [
            'title' => 'Find party vendors',
            'vendors' => Vendor::all($search, $service),
            'search' => $search,
            'service' => $service,
            'serviceTypes' => Vendor::SERVICE_TYPES,
        ]);
    }

    public static function manage(): void
    {
        require_admin();
        render('vendors/manage', [
            'title' => 'Manage vendors',
            'vendors' => Vendor::all(),
        ]);
    }

    public static function create(): void
    {
        require_admin();
        render('vendors/form', ['title' => 'Add vendor', 'vendor' => null, 'errors' => []]);
    }

    public static function store(): void
    {
        require_admin();
        [$values, $errors] = self::validatedInput();
        if ($errors) {
            render('vendors/form', ['title' => 'Add vendor', 'vendor' => null, 'errors' => $errors, 'values' => $values]);
            return;
        }
        Vendor::create($values['name'], $values['service_type'], $values['price'], $values['contact_email']);
        flash('success', 'Vendor added to the directory.');
        redirect('vendors/manage');
    }

    public static function edit(): void
    {
        require_admin();
        $vendor = Vendor::find(request_id());
        if (!$vendor) {
            throw new HttpException(404, 'Vendor not found.');
        }
        render('vendors/form', ['title' => 'Edit vendor', 'vendor' => $vendor, 'errors' => []]);
    }

    public static function update(): void
    {
        require_admin();
        $id = post_int('vendor_id');
        if (!Vendor::find($id)) {
            throw new HttpException(404, 'Vendor not found.');
        }
        [$values, $errors] = self::validatedInput();
        if ($errors) {
            $values['vendor_id'] = $id;
            render('vendors/form', ['title' => 'Edit vendor', 'vendor' => $values, 'errors' => $errors, 'values' => $values]);
            return;
        }
        Vendor::update($id, $values['name'], $values['service_type'], $values['price'], $values['contact_email']);
        flash('success', 'Vendor updated.');
        redirect('vendors/manage');
    }

    public static function delete(): void
    {
        require_admin();
        $id = post_int('vendor_id');
        if (!Vendor::find($id)) {
            throw new HttpException(404, 'Vendor not found.');
        }
        Vendor::delete($id);
        flash('success', 'Vendor deleted.');
        redirect('vendors/manage');
    }

    private static function validatedInput(): array
    {
        $values = [
            'name' => post_string('name'),
            'service_type' => post_string('service_type'),
            'price' => post_string('price'),
            'contact_email' => strtolower(post_string('contact_email')),
        ];
        $errors = [];
        if (strlen($values['name']) < 2 || strlen($values['name']) > 120) {
            $errors[] = 'Vendor name must be between 2 and 120 characters.';
        }
        if (!in_array($values['service_type'], Vendor::SERVICE_TYPES, true)) {
            $errors[] = 'Choose a valid service type.';
        }
        $price = whole_rwf_amount($values['price']);
        if ($price === null) {
            $errors[] = 'Enter a whole RWF price from 0 to 9,999,999.';
        } else {
            $values['price'] = $price;
        }
        if (!validate_email($values['contact_email'])) {
            $errors[] = 'Enter a valid contact email.';
        }
        return [$values, $errors];
    }
}
