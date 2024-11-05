<h1 class="text-3xl font-bold mb-4">Admin Panel - High Bass Audio</h1>

<div class="products mb-8">
    <h2 class="text-2xl font-semibold mb-2">Products</h2>
    <table class="min-w-full bg-white border border-gray-200 mt-4">
        <thead>
            <tr>
                <th class="text-start py-2 px-4 border-b">Image</th>
                <th class="text-start py-2 px-4 border-b">Title</th>
                <th class="text-start py-2 px-4 border-b">Price</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (array_slice(array_reverse($products), 0, 3) as $product): ?>
                <tr>
                    <td class="py-2 px-4 border-b border-r">
                        <img src="/images/<?= $product->image_path ?>" alt="<?= $product->title ?>" class="max-w-24">
                    </td>
                    <td class="py-2 px-4 border-b border-r"><?= $product->title ?></td>
                    <td class="py-2 px-4 border-b border-r">€ <?= $product->price ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="4">
                    <div class="mt-4 mb-4 text-center">
                        <a href="/admin/products" class="text-blue-500 hover:text-blue-700 hover:underline">View all products</a>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>