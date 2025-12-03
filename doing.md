so i had an interview recently stage 3, and got disquaified because i did not have an onvious multivendor plus logistics implementation activity in corp level. that said, i decided to build something to use to work on improving the skill...

first, i had to run db migration to create the users table, the shops table, the products table, the orders table, the sub orders table, the order items table, the order items table before i started anything.

then i created eloquent models with relationships, thats for the user, shop, product, suborder, orderitem. 

then i created the services classes i needed including logistics service, payment service.

then i created the marketplace configuration 

most importantly, i made sure all migrations include proper indexes, foreign key constraints, and soft deletes where appropriate.

literally, i have finished the skeleton of this project. i am considering what made me miss the interview, and i would implement a geospatial logic in my logistic service. i have taken a view that i would implement the haversine formula since it is a math formula to calculate the distance between two points on a sphere, in this case, the earth is the sphere. so literally, i think when they ask again how i would handle calculation if the google maps api goes down, i can literally know i implemented a haversine fallback in my service layer.