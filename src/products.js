// Edit this file to change products, prices and reviews.
export const CURRENCY = '$';
export const FREE_SHIPPING_AT = 300;

export const PRODUCTS = [
  {
    id: 'cognac-biker',
    name: 'Cognac Asymmetric Biker',
    category: 'biker',
    categoryLabel: 'Biker Jacket',
    price: 289,
    oldPrice: 349,
    image: 'images/cognac-asymmetric-biker.jpg',
    color3d: 'cognac',
    badge: 'Bestseller',
    reviews: 214,
    desc: 'The rebel classic. Asymmetric YKK® zip, snap-down lapels, ribbed shoulder panels and zipped cuffs — in glowing cognac full-grain leather that deepens with every ride.',
  },
  {
    id: 'midnight-hooded',
    name: 'Midnight Hooded Jacket',
    category: 'hooded',
    categoryLabel: 'Hooded Jacket',
    price: 259,
    image: 'images/midnight-hooded-jacket.webp',
    color3d: 'black',
    badge: 'New',
    reviews: 132,
    desc: 'Street-ready black lambskin with a removable fleece hood, twin chest zip pockets and a clean trucker silhouette. The jacket you will reach for every single day.',
  },
  {
    id: 'onyx-hooded-moto',
    name: 'Onyx Hooded Moto',
    category: 'hooded',
    categoryLabel: 'Hooded Jacket',
    price: 279,
    image: 'images/onyx-hooded-moto.webp',
    color3d: 'black',
    reviews: 98,
    desc: 'Double-zip storm front, quilted inner lining and a detachable jersey hood. Built from buttery black leather with gunmetal hardware for a modern moto look.',
  },
  {
    id: 'camel-cafe',
    name: 'Camel Quilted Café Racer',
    category: 'cafe',
    categoryLabel: 'Café Racer',
    price: 299,
    oldPrice: 359,
    image: 'images/camel-quilted-cafe-racer.jpg',
    color3d: 'camel',
    badge: 'Limited',
    reviews: 176,
    desc: 'Hand-waxed distressed camel leather with diamond-quilted shoulders, snap-tab collar and side adjusters. Vintage character from day one.',
  },
  {
    id: 'tobacco-cafe',
    name: 'Tobacco Classic Café Racer',
    category: 'cafe',
    categoryLabel: 'Café Racer',
    price: 269,
    image: 'images/tobacco-cafe-racer.webp',
    color3d: 'tobacco',
    reviews: 151,
    desc: 'A minimalist icon: snap stand collar, slanted zip chest pockets and a warm plaid lining. Soft tobacco-brown leather that fits like a second skin.',
  },
];

export const SIZES = ['S', 'M', 'L', 'XL', 'XXL'];

export const REVIEWS = [
  { name: 'Ahmed R.', city: 'Lahore', text: 'The stitching is unreal. You can feel the quality the moment you put it on — three winters in and it only looks better.', product: 'Cognac Asymmetric Biker' },
  { name: 'Daniel K.', city: 'London', text: 'Ordered custom sizing and it fits perfectly across the shoulders. Packaging felt like opening a luxury watch.', product: 'Camel Quilted Café Racer' },
  { name: 'Sara M.', city: 'Dubai', text: 'Bought the hooded one for my husband and ended up ordering one for myself. Soft, warm and gorgeous.', product: 'Midnight Hooded Jacket' },
  { name: 'Lucas P.', city: 'Toronto', text: 'Rode 2,000 km through rain and wind. The zips never snagged once. Worth every penny.', product: 'Onyx Hooded Moto' },
];
