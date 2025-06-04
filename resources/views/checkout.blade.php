<html>
<body>
    <form name="ipay88-checkout-form" method="POST" action="{{ $url }}" style="display: none;">
        @foreach ($body as $key => $value)
            <input name="{{ $key }}" value="{{ $value }}">
        @endforeach
    </form>
    <div>Redirecting to payment gateway...</div>
    <script>window.onload = function() { document.forms['ipay88-checkout-form'].submit() }</script>
</body>
</html>
