<?php

namespace App\Models;

class AdminModel extends BaseModel {
    public function getProducts() {
        $query = "SELECT * FROM product";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $products = $stmt->fetchAll(\PDO::FETCH_OBJ);
        return $products;
    }

    public function getProduct($id) {
        $query = "SELECT * FROM product WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch(\PDO::FETCH_OBJ);
        return $product;
    }

    public function getArtists() {
        $query = "SELECT * FROM artist";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $artists = $stmt->fetchAll(\PDO::FETCH_OBJ);
        return $artists;
    }

    public function getAlbums() {
        $query = "SELECT * FROM album";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $albums = $stmt->fetchAll(\PDO::FETCH_OBJ);
        return $albums;
    }

    public function getTracks() {
        $query = "SELECT 
        t.id AS track_id,
        t.title AS track_title,
        a.title AS album_title,
        ar.artist_name
        FROM 
        track t
        LEFT JOIN 
        album a ON t.album_id = a.id
        LEFT JOIN 
        artist_track at ON t.id = at.track_id
        LEFT JOIN 
        artist ar ON at.artist_id = ar.id
        ";
    
        $stmt = $this->db->prepare($query);
        $stmt->execute();
    
        $tracks = $stmt->fetchAll(\PDO::FETCH_OBJ);
    
        return $tracks;
    }

public function saveMusic($data) {
    $artists = $data['artists'];
    $firstnames = $data['firstnames'];
    $lastnames = $data['lastnames'];
    $countries = $data['countries'];
    $type = $data['type'];
    $tracks = $data['tracks'];
    $trackFiles = $data['trackFiles']; 
    $artistNames = isset($data['artistNames']) ? $data['artistNames'] : []; 
    $pricePerTrack = $data['pricePerTrack'];
    $productTitle = $data['productTitle'];
    $productDescription = $data['productDescription'];
    $productPrice = $data['productPrice'];
    $productImage = $data['productImage'];

    $artistIdMap = [];
    $album_id = null;

    // Insert artists or get existing artist IDs
    for ($i = 0; $i < count($artists); $i++) {
        $artist_name = $artists[$i];
        $firstname = $firstnames[$i];
        $lastname = $lastnames[$i];
        $country = $countries[$i];

        // Check if artist already exists
        $sql_check = "SELECT id FROM artist WHERE artist_name = ?";
        $stmt_check = $this->db->prepare($sql_check);
        $stmt_check->execute([$artist_name]);
        $artist_id = $stmt_check->fetchColumn();

        if (!$artist_id) {
            // Insert new artist
            $sql = "INSERT INTO artist (artist_name, firstname, lastname, country) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$artist_name, $firstname, $lastname, $country]);
            $artist_id = $this->db->lastInsertId();
        }

        $artistIdMap[$artist_name] = $artist_id; // Map artist name to ID
    }

    // Check if it's an album type and save album details if applicable
    if ($type == 'album') {
        $album_title = $data['albumTitle'];
        $album_file = $data['albumFile'];

        // Check if the album file is uploaded correctly
        if ($album_file && $album_file['error'] == UPLOAD_ERR_OK) {
            // Extract the file name from the album file
            $album_file_name = uniqid() . '-' . basename($album_file['name']); // Correctly extract the file name

            // Insert album into the album table
            $sql_album = "INSERT INTO album (title, file_path, track_price) VALUES (?, ?, ?)";
            $stmt_album = $this->db->prepare($sql_album);
            $stmt_album->execute([$album_title, $album_file_name, $pricePerTrack]);
            $album_id = $this->db->lastInsertId();
        } else {
            throw new \Exception('Album file upload failed');
        }
    }

    // Insert tracks
    $track_ids = [];
    for ($i = 0; $i < count($tracks); $i++) {
        $track_title = $tracks[$i];
        $track_file_name = uniqid() . '-' . basename($trackFiles[$i]); // Just saving the filename with a unique ID prefix

        // Insert track into the track table
        $sql_track = "INSERT INTO track (title, file_path, album_id) VALUES (?, ?, ?)";
        $stmt_track = $this->db->prepare($sql_track);
        $stmt_track->execute([$track_title, $track_file_name, $album_id]);
        $track_id = $this->db->lastInsertId();
        $track_ids[] = $track_id;

        // Insert into artist_track table for each artist
        if ($type == 'album') {
            foreach ($artistNames[$track_title] as $artist_name) {
                $artist_id = $artistIdMap[$artist_name];
                if ($artist_id > 0) {
                    $sql_artist_track = "INSERT INTO artist_track (artist_id, track_id) VALUES (?, ?)";
                    $stmt_artist_track = $this->db->prepare($sql_artist_track);
                    $stmt_artist_track->execute([$artist_id, $track_id]);
                } else {
                    return "Invalid artist ID for $artist_name";
                }
            }
        } else {
            // For single type, use the artists added initially
            foreach ($artistIdMap as $artist_id) {
                $sql_artist_track = "INSERT INTO artist_track (artist_id, track_id) VALUES (?, ?)";
                $stmt_artist_track = $this->db->prepare($sql_artist_track);
                $stmt_artist_track->execute([$artist_id, $track_id]);
            }
        }
    }

    // Save product image
    if ($productImage && $productImage['error'] == UPLOAD_ERR_OK) {
        $target_dir = "images/";
        $product_image_name = uniqid() . '-' . basename($productImage['name']);
        $target_file = $target_dir . $product_image_name;
        move_uploaded_file($productImage['tmp_name'], $target_file);
    } else {
        throw new \Exception('Product image upload failed');
    }

    // Save product information
    $sql_product = "INSERT INTO product (title, description, price, image_path, album_id, track_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_product = $this->db->prepare($sql_product);
    if ($type == 'album') {
        $stmt_product->execute([$productTitle, $productDescription, $productPrice, $product_image_name, $album_id, null]);
    } else {
        foreach ($track_ids as $track_id) {
            $stmt_product->execute([$productTitle, $productDescription, $productPrice, $product_image_name, null, $track_id]);
        }
    }

    header('Location: /admin');
}

public function editProduct($id, $data) {
    // Update product details
    $sql = "UPDATE product SET title = :title, description = :description, price = :price";
    
    // Check if image path is set
    if (isset($data['image_path'])) {
        $sql .= ", image_path = :image_path";
    }
    
    $sql .= " WHERE id = :id";

    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':title', $data['title']);
    $stmt->bindParam(':description', $data['description']);
    $stmt->bindParam(':price', $data['price']);
    if (isset($data['image_path'])) {
        $stmt->bindParam(':image_path', $data['image_path']);
    }
    $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
    
    return $stmt->execute();
}

public function deleteProduct($id) {
    // Fetch product details
    $query = "SELECT album_id, track_id FROM product WHERE id = :id";
    $stmt = $this->db->prepare($query);
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch(\PDO::FETCH_OBJ);

    if ($product) {
        if ($product->album_id) {
            // Delete associated tracks and links in artist_track
            $query = "SELECT id FROM track WHERE album_id = :album_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['album_id' => $product->album_id]);
            $tracks = $stmt->fetchAll(\PDO::FETCH_OBJ);

            foreach ($tracks as $track) {
                // Delete links in artist_track
                $query = "DELETE FROM artist_track WHERE track_id = :track_id";
                $stmt = $this->db->prepare($query);
                $stmt->execute(['track_id' => $track->id]);

                // Delete track
                $query = "DELETE FROM track WHERE id = :track_id";
                $stmt = $this->db->prepare($query);
                $stmt->execute(['track_id' => $track->id]);
            }

            // Delete product
            $query = "DELETE FROM product WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $id]);

            // Delete album
            $query = "DELETE FROM album WHERE id = :album_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['album_id' => $product->album_id]);

        } else {
            // Delete link in artist_track
            $query = "DELETE FROM artist_track WHERE track_id = :track_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['track_id' => $product->track_id]);

            // Delete track
            $query = "DELETE FROM track WHERE id = :track_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['track_id' => $product->track_id]);

            // Delete product
            $query = "DELETE FROM product WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $id]);
        }

        header('Location: /admin');
    } else {
        throw new \Exception('Product not found');
    }
}
}