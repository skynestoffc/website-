public function store(Request $request, Product $product)
{
    $request->validate([
        'bulk_text' => ['required','string'],
    ]);

    $lines = preg_split("/\r\n|\n|\r/", trim($request->bulk_text));
    $rows = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;

        $rows[] = [
            'product_id' => $product->id,
            'payload_text' => $line,
            'status' => 'available',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    \DB::table('product_stocks')->insert($rows);

    return back()->with('ok', 'Stok ditambahkan: '.count($rows));
}
