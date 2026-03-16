<div class="container py-8 mx-auto px-4">
    <h1 class="text-3xl font-bold mb-6">Image Toolkit Test & Documentation</h1>

    <% if $TestImage %>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
            <div class="bg-gray-100 p-4 rounded shadow">
                <h2 class="text-xl font-semibold mb-2">Original Image</h2>
                <div class="mb-4">
                    $TestImage.ScaleWidth(600)
                </div>
                <p class="text-sm text-gray-600">Original dimensions: $TestImage.Width x $TestImage.Height px</p>
            </div>

            <div class="bg-gray-100 p-4 rounded shadow">
                <h2 class="text-xl font-semibold mb-2">Watermark Effect</h2>
                <div class="mb-4">
                    <% if $TestLogo %>
                        $TestImage.ScaleWidth(600).Watermark($TestLogo.ID, 'bottom-right', '5%', 0.6, '20%')
                    <% else %>
                        $TestImage.ScaleWidth(600).Watermark
                        <p class="text-red-500 text-sm mt-2 italic">Note: Custom logo not found in module images, using default if configured.</p>
                    <% end_if %>
                </div>
                <code class="block bg-gray-800 text-white p-2 rounded text-xs">
                    \$Image.Watermark(\$Logo.ID, 'bottom-right', '5%', 0.6, '20%')
                </code>
            </div>

            <div class="bg-gray-100 p-4 rounded shadow">
                <h2 class="text-xl font-semibold mb-2">Color Overlay</h2>
                <div class="mb-4">
                    $TestImage.ScaleWidth(600).ColorOverlay('#3b82f6', 0.4)
                </div>
                <code class="block bg-gray-800 text-white p-2 rounded text-xs">
                    \$Image.ColorOverlay('#3b82f6', 0.4)
                </code>
            </div>

            <div class="bg-gray-100 p-4 rounded shadow">
                <h2 class="text-xl font-semibold mb-2">Chaining (AutoOrient + Overlay + Watermark)</h2>
                <div class="mb-4">
                    <% if $TestLogo %>
                        $TestImage.AutoOrient.ScaleWidth(600).ColorOverlay('#10b981', 0.3).Watermark($TestLogo.ID)
                    <% else %>
                        $TestImage.AutoOrient.ScaleWidth(600).ColorOverlay('#10b981', 0.3).Watermark
                    <% end_if %>
                </div>
                <code class="block bg-gray-800 text-white p-2 rounded text-xs">
                    \$Image.AutoOrient.ColorOverlay('#10b981', 0.3).Watermark
                </code>
            </div>
        </div>
    <% else %>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-8" role="alert">
            <p class="font-bold">Example image missing!</p>
            <p>Please place an image at <code>silverstripe-image-toolkit/images/example-image.jpg</code>.</p>
        </div>
    <% end_if %>

    <div class="prose max-w-none bg-white p-6 rounded shadow">
        <h2>Documentation</h2>
        <p>This controller is only active in <strong>dev mode</strong> and requires <strong>ADMIN permissions</strong>.</p>
        
        <h3>Watermark Settings</h3>
        <ul>
            <li><strong>Logo:</strong> Path to image or Image object</li>
            <li><strong>Position:</strong> center, top-left, top-right, bottom-left, bottom-right</li>
            <li><strong>Padding:</strong> Distance from edge (px or %)</li>
            <li><strong>Opacity:</strong> 0.0 to 1.0</li>
            <li><strong>Width:</strong> Relative width to background image (%)</li>
        </ul>

        <h3>Configuration</h3>
        <pre class="bg-gray-100 p-4 rounded"><code>Netwerkstatt\ImageToolkit\Extension\Watermark:
  default_watermark_logo: 'themes/mytheme:images/logo.png'
  default_watermark_opacity: 0.5
  default_watermark_position: 'bottom-right'
  default_watermark_padding: '5%'
  default_watermark_width: '25%'</code></pre>
    </div>
</div>
