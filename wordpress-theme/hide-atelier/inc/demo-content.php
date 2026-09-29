<?php
/**
 * Default page content created by the setup wizard. Store details are
 * inserted with [ha_store] shortcodes so they update from the Customizer.
 *
 * Please have a lawyer review the policies for your business before launch.
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pages: slug => [ title, template, content ].
 */
function ha_demo_pages() {
	$pages = array();

	$pages['home'] = array( 'Home', '', '' );

	$pages['about'] = array(
		'Our Craft',
		'templates/about.php',
		'<p>Every [ha_store field="name"] jacket starts as a single hide chosen by hand. We cut each panel to follow the grain, stitch every seam twice where it matters and finish the edges by hand — so the jacket moves with you and gets better with every year you wear it.</p>',
	);

	$pages['contact'] = array(
		'Contact',
		'templates/contact.php',
		'<p>Questions about sizing, an order or a custom fit? Message us and a real person from our workshop will reply — usually within a few hours.</p>',
	);

	$pages['size-guide'] = array(
		'Size Guide',
		'templates/size-guide.php',
		'<p>Our jackets are cut for a tailored fit with room for a hoodie or a knit underneath. Use a soft measuring tape and measure over a T-shirt, not over a jacket.</p>',
	);

	$pages['shipping-policy'] = array(
		'Shipping Policy',
		'templates/policy.php',
		<<<'HTML'
<p><em>Last updated: [ha_store field="date"]</em></p>
<p>This Shipping Policy explains how and when orders placed on [ha_store field="site"] are delivered. By placing an order you agree to the terms below.</p>

<h2>Order processing</h2>
<p>Ready-to-ship jackets are packed and dispatched within <strong>[ha_store field="dispatch_days"] working days</strong> after your order is confirmed. Orders placed on Sundays or public holidays are processed on the next working day. Made-to-measure and custom orders are cut and stitched for you and take longer; the delivery estimate is shown on the product page and confirmed by email.</p>
<p>For Cash on Delivery orders we may call or send a WhatsApp message to confirm your order and address before dispatch. Orders we cannot confirm within 48 hours may be cancelled.</p>

<h2>Delivery within Pakistan</h2>
<ul>
<li>Delivered by trusted courier partners to all major cities and most towns.</li>
<li>Estimated delivery: <strong>[ha_store field="domestic_days"] working days</strong> after dispatch. Remote areas can take 2–3 days longer.</li>
<li><strong>Free delivery</strong> on orders over [ha_store field="free_ship"]. A standard delivery fee applies below this amount and is shown at checkout.</li>
<li>Cash on Delivery is available for orders delivered within Pakistan. Please keep the exact amount ready.</li>
</ul>

<h2>International shipping</h2>
<ul>
<li>We ship worldwide with express couriers. Estimated delivery: <strong>[ha_store field="international_days"] working days</strong> after dispatch.</li>
<li>International orders must be paid online at checkout; Cash on Delivery is not available outside Pakistan.</li>
<li>Prices are shown in your local currency for convenience. Customs duties, import taxes or brokerage fees charged by your country are not included and are the responsibility of the customer.</li>
</ul>

<h2>Tracking your order</h2>
<p>As soon as your jacket is dispatched you will receive the courier name and tracking number by email and/or SMS. If you have not received it within the dispatch time above, contact us at [ha_store field="email"] or on [ha_store field="whatsapp"].</p>

<h2>Address changes & failed deliveries</h2>
<p>Please double-check your address and phone number at checkout. We can change the address only before the parcel is dispatched. If a delivery fails because the address was incorrect or the parcel was refused, the return shipping cost and any re-delivery fee will be charged to the customer.</p>

<h2>Damaged or lost parcels</h2>
<p>If your parcel arrives damaged, please take photos before opening it and contact us within 48 hours of delivery. If a parcel is lost in transit we will work with the courier and send a replacement or a full refund.</p>

<h2>Contact</h2>
<p>[ha_store field="name"]<br>[ha_store field="address"]<br>Email: [ha_store field="email"] · Phone: [ha_store field="phone"]</p>
HTML
	);

	$pages['refund-policy'] = array(
		'Refund & Return Policy',
		'templates/policy.php',
		<<<'HTML'
<p><em>Last updated: [ha_store field="date"]</em></p>
<p>We want you to love your jacket. If something is not right, this policy explains how returns, exchanges and refunds work.</p>

<h2>Return window</h2>
<p>You can request a return or exchange within <strong>[ha_store field="return_days"] days</strong> of receiving your order. To start, email [ha_store field="email"] or message us on [ha_store field="whatsapp"] with your order number and the reason for the return.</p>

<h2>Conditions</h2>
<ul>
<li>The jacket must be unworn, unwashed and in its original condition, with all tags and packaging.</li>
<li>Items with signs of wear, perfume, stains or alterations cannot be accepted.</li>
<li>Made-to-measure, personalised (monogrammed) and custom orders are made only for you and cannot be returned, except when they are faulty.</li>
<li>Items bought on final sale cannot be returned unless faulty.</li>
</ul>

<h2>Size exchanges</h2>
<p>Ordered the wrong size? We will exchange it for another size of the same jacket, subject to availability. Please check our <a href="/size-guide/">Size Guide</a> before ordering; we are happy to help you choose on WhatsApp.</p>

<h2>Faulty or wrong items</h2>
<p>If your jacket arrives damaged, faulty or is not the item you ordered, contact us within 48 hours of delivery with photos. We will arrange a free pick-up and send a replacement or a full refund, including delivery charges.</p>

<h2>Return shipping</h2>
<p>Unless the item is faulty or incorrect, the customer pays the cost of sending the return to us. Please use a trackable service; we cannot be responsible for returns lost on the way.</p>

<h2>Refunds</h2>
<ul>
<li>Once we receive and inspect your return, we will email you to confirm whether the refund is approved.</li>
<li>Approved refunds are processed within <strong>7–10 working days</strong>.</li>
<li>Online payments are refunded to the original payment method. Cash on Delivery orders are refunded by bank transfer, JazzCash or Easypaisa to an account you provide.</li>
<li>Original delivery charges are non-refundable unless the item was faulty or incorrect.</li>
</ul>

<h2>Order cancellation</h2>
<p>You can cancel an order free of charge before it is dispatched. Custom and made-to-measure orders can be cancelled only before cutting has started.</p>

<h2>Contact</h2>
<p>[ha_store field="name"] · [ha_store field="email"] · [ha_store field="phone"]</p>
HTML
	);

	$pages['privacy-policy'] = array(
		'Privacy Policy',
		'templates/policy.php',
		<<<'HTML'
<p><em>Last updated: [ha_store field="date"]</em></p>
<p>This Privacy Policy explains how [ha_store field="name"] ("we", "us") collects, uses and protects your personal information when you visit [ha_store field="site"] or place an order.</p>

<h2>Information we collect</h2>
<ul>
<li><strong>Order details:</strong> your name, email, phone number, billing and delivery address, and the products you buy.</li>
<li><strong>Payment information:</strong> online payments are processed securely by our payment providers. We never see or store your full card details.</li>
<li><strong>Account details:</strong> if you create an account, your login email and order history.</li>
<li><strong>Messages:</strong> what you send us through the contact form, email or WhatsApp.</li>
<li><strong>Technical data:</strong> IP address, browser type, pages visited, and cookies (see below).</li>
</ul>

<h2>How we use your information</h2>
<ul>
<li>To process, deliver and support your orders, including sharing your name, phone and address with our courier partners.</li>
<li>To reply to your questions and provide customer service.</li>
<li>To prevent fraud and keep our website secure.</li>
<li>To send newsletters and offers only if you subscribed. You can unsubscribe at any time.</li>
<li>To meet legal, tax and accounting obligations.</li>
</ul>

<h2>Cookies</h2>
<p>We use essential cookies to keep your shopping cart, remember your currency and keep you logged in. With your consent we may also use analytics cookies to understand how the site is used. You can delete or block cookies in your browser settings, but parts of the shop may not work without them.</p>

<h2>Sharing your information</h2>
<p>We do not sell your personal information. We share it only with service providers who help us run the shop — couriers, payment processors, website hosting and email services — and only as much as they need to do their job, or when required by law.</p>

<h2>How long we keep data</h2>
<p>Order records are kept for as long as required for tax and accounting purposes. Other data is kept only as long as needed for the purposes above, after which it is deleted or anonymised.</p>

<h2>Your rights</h2>
<p>You can ask us to access, correct or delete your personal information, or to stop sending you marketing emails. Contact us at [ha_store field="email"] and we will respond within 30 days.</p>

<h2>Security</h2>
<p>Our website uses SSL encryption, and access to customer data is limited to staff who need it. No method of transmission over the internet is 100% secure, but we take reasonable steps to protect your information.</p>

<h2>Children</h2>
<p>Our shop is not directed at children under 13, and we do not knowingly collect their personal information.</p>

<h2>Changes to this policy</h2>
<p>We may update this policy from time to time. The latest version will always be on this page with the date it was last updated.</p>

<h2>Contact us</h2>
<p>[ha_store field="name"]<br>[ha_store field="address"]<br>Email: [ha_store field="email"] · Phone: [ha_store field="phone"]</p>
HTML
	);

	$pages['terms'] = array(
		'Terms & Conditions',
		'templates/policy.php',
		<<<'HTML'
<p><em>Last updated: [ha_store field="date"]</em></p>
<p>These terms apply to all purchases from [ha_store field="site"]. By using this website or placing an order, you agree to them.</p>

<h2>Products</h2>
<p>Leather is a natural material: small variations in grain, colour and texture are part of its character and are not defects. Colours may look slightly different on different screens.</p>

<h2>Prices & currency</h2>
<p>Prices are set in our store currency. Where we show another currency, the price is converted at a current exchange rate and rounded; the amount shown at checkout is the amount you pay. Your bank may charge its own conversion fees. We may change prices at any time, but not for orders already confirmed.</p>

<h2>Orders</h2>
<p>An order is accepted when we send the order confirmation. We may cancel an order if a product is unavailable, if there is an obvious pricing error, or if we cannot verify the order. If you have already paid, you will receive a full refund.</p>

<h2>Payment</h2>
<p>We accept the payment methods shown at checkout. Cash on Delivery is available within Pakistan only.</p>

<h2>Shipping, returns & refunds</h2>
<p>Please see our <a href="/shipping-policy/">Shipping Policy</a> and <a href="/refund-policy/">Refund & Return Policy</a>.</p>

<h2>Warranty</h2>
<p>Our stitching warranty covers seams that come undone under normal use. It does not cover wear and tear, accidents, burns, improper cleaning or alterations made by others.</p>

<h2>Intellectual property</h2>
<p>All content on this website — photos, text, designs and logos — belongs to [ha_store field="name"] and may not be copied without permission.</p>

<h2>Limitation of liability</h2>
<p>To the extent permitted by law, our liability for any order is limited to the amount you paid for that order.</p>

<h2>Governing law</h2>
<p>These terms are governed by the laws of Pakistan, and disputes are subject to the courts of the city where our business is registered.</p>

<h2>Contact</h2>
<p>[ha_store field="email"] · [ha_store field="phone"]</p>
HTML
	);

	$pages['faq'] = array(
		'FAQ',
		'templates/policy.php',
		<<<'HTML'
<h2>Is the leather genuine?</h2>
<p>Yes. Every jacket is made from 100% genuine leather — never PU or faux leather.</p>
<h2>How do I choose my size?</h2>
<p>Measure your chest and compare it with our <a href="/size-guide/">Size Guide</a>. Between two sizes, choose the larger one if you like to layer. You can also send us your height and weight on WhatsApp and we will recommend a size.</p>
<h2>Do you offer Cash on Delivery?</h2>
<p>Yes, across Pakistan. International orders are paid online at checkout.</p>
<h2>How long does delivery take?</h2>
<p>We dispatch within [ha_store field="dispatch_days"] working days. Delivery takes [ha_store field="domestic_days"] working days in Pakistan and [ha_store field="international_days"] working days internationally.</p>
<h2>Can I return or exchange my jacket?</h2>
<p>Yes, within [ha_store field="return_days"] days if it is unworn and in its original condition. See our <a href="/refund-policy/">Refund & Return Policy</a>.</p>
<h2>How do I care for leather?</h2>
<p>Hang it on a wide hanger, keep it away from direct heat, wipe spills with a soft dry cloth and condition the leather once or twice a year. Never machine-wash.</p>
HTML
	);

	return $pages;
}

/**
 * Demo products (prices in PKR). Images live in assets/images/products.
 */
function ha_demo_products() {
	return array(
		array(
			'sku'      => 'HA-COGNAC-BIKER',
			'name'     => 'The Rebel | Men\'s Cognac Asymmetric Biker Jacket',
			'cat'      => 'Biker Jackets',
			'price'    => 29500,
			'regular'  => 34500,
			'image'    => 'cognac-asymmetric-biker.jpg',
			'gallery'  => array( 'cognac-asymmetric-biker-detail.jpg', 'size-chart.png' ),
			'material' => 'Full-grain cowhide leather',
			'subtitle' => 'An asymmetric zip, snap-down lapels and quilted shoulders — the rebel classic.',
			'badge'    => 'Bestseller',
			'features' => "Asymmetric YKK® front zip\nSnap-down lapel collar\nRibbed quilted shoulder panels\nZipped cuffs and three zip pockets",
			'desc'     => 'The motorcycle jacket that started it all. Cut from glowing cognac full-grain leather that deepens in colour with every ride, it features an asymmetric YKK® zip, snap-down lapels, ribbed shoulder panels for movement and zipped cuffs for a close fit over gloves. Fully lined, with an inner pocket for your phone.',
		),
		array(
			'sku'      => 'HA-MIDNIGHT-HOOD',
			'name'     => 'The Nomad | Men\'s Black Hooded Leather Jacket',
			'cat'      => 'Hooded Jackets',
			'price'    => 26500,
			'image'    => 'midnight-hooded-jacket.webp',
			'gallery'  => array( 'midnight-hooded-jacket-detail.jpg', 'size-chart.png' ),
			'material' => 'Soft lambskin leather',
			'subtitle' => 'A clean trucker cut with a removable fleece hood for everyday wear.',
			'badge'    => 'New',
			'features' => "Removable fleece hood\nTwin zipped chest pockets\nSide-entry hand pockets\nSoft quilted lining",
			'desc'     => 'Street-ready black lambskin with a removable fleece hood, twin zipped chest pockets and a clean trucker silhouette. Light enough for autumn evenings, warm enough for winter — the jacket you will reach for every single day.',
		),
		array(
			'sku'      => 'HA-ONYX-MOTO',
			'name'     => 'The Onyx | Men\'s Hooded Moto Leather Jacket',
			'cat'      => 'Hooded Jackets',
			'price'    => 27500,
			'image'    => 'onyx-hooded-moto.webp',
			'gallery'  => array( 'onyx-hooded-moto-detail.jpg', 'size-chart.png' ),
			'material' => 'Top-grain sheep leather',
			'subtitle' => 'Double storm front and a detachable jersey hood.',
			'features' => "Double zip storm front\nDetachable jersey hood\nQuilted inner lining\nGunmetal hardware",
			'desc'     => 'A modern moto jacket with a double-zip storm front, quilted inner lining and a detachable jersey hood. Built from buttery black sheep leather with gunmetal hardware.',
		),
		array(
			'sku'      => 'HA-CAMEL-CAFE',
			'name'     => 'The Classic | Men\'s Camel Quilted Café Racer',
			'cat'      => 'Café Racer Jackets',
			'price'    => 31500,
			'regular'  => 36000,
			'image'    => 'camel-quilted-cafe-racer.jpg',
			'gallery'  => array( 'camel-quilted-cafe-racer-detail.jpg', 'size-chart.png' ),
			'material' => 'Hand-waxed distressed leather',
			'subtitle' => 'Diamond-quilted shoulders and a snap-tab collar with vintage character.',
			'badge'    => 'Limited',
			'features' => "Diamond-quilted shoulders\nSnap-tab stand collar\nTwo zipped chest pockets\nSide waist adjusters",
			'desc'     => 'Hand-waxed distressed camel leather with diamond-quilted shoulders, a snap-tab stand collar and side adjusters. It has the character of a vintage find from the very first day.',
		),
		array(
			'sku'      => 'HA-TOBACCO-CAFE',
			'name'     => 'The Spy | Men\'s Tobacco Café Racer Jacket',
			'cat'      => 'Café Racer Jackets',
			'price'    => 28500,
			'image'    => 'tobacco-cafe-racer.webp',
			'gallery'  => array( 'tobacco-cafe-racer-detail.jpg', 'size-chart.png' ),
			'material' => 'Top-grain sheep leather',
			'subtitle' => 'A snap stand collar and slanted zip pockets — minimal and timeless.',
			'features' => "Snap-button stand collar\nSlanted zipped chest pockets\nWarm plaid lining\nTwo inside pockets",
			'desc'     => 'A minimalist icon with a snap stand collar, slanted zipped chest pockets and a warm plaid lining. Soft tobacco-brown leather that fits like a second skin.',
		),
	);
}
