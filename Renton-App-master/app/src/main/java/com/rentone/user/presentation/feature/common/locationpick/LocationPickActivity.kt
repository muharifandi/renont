package com.rentone.user.presentation.feature.common.locationpick

import android.app.Activity
import android.content.Intent
import android.os.Bundle
import android.view.MenuItem
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.isVisible
import com.google.android.gms.location.LocationServices
import com.google.android.gms.maps.CameraUpdateFactory
import com.google.android.gms.maps.GoogleMap
import com.google.android.gms.maps.OnMapReadyCallback
import com.google.android.gms.maps.SupportMapFragment
import com.google.android.gms.maps.model.LatLng
import com.google.android.libraries.places.api.Places
import com.google.android.libraries.places.api.model.Place
import com.google.android.libraries.places.widget.AutocompleteSupportFragment
import com.rentone.user.R
import com.rentone.user.databinding.ActivityLocationPickBinding
import dagger.hilt.android.AndroidEntryPoint
import timber.log.Timber
import java.util.Locale
import com.rentone.user.core.util.applyExitTransition

@AndroidEntryPoint
class LocationPickActivity : AppCompatActivity(), OnMapReadyCallback {

    private lateinit var binding: ActivityLocationPickBinding
    private var map: GoogleMap? = null
    private lateinit var currentPosition: LatLng

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityLocationPickBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        val latitude = intent.getDoubleExtra("latitude", 0.0)
        val longitude = intent.getDoubleExtra("longitude", 0.0)
        intent.getStringExtra("title")?.let { title = it }

        currentPosition = LatLng(latitude, longitude)

        if (!Places.isInitialized()) {
            Places.initialize(applicationContext, getString(R.string.google_maps_key), Locale.US)
        }

        val autocompleteFragment = supportFragmentManager
            .findFragmentById(R.id.autocomplete_fragment) as AutocompleteSupportFragment
        autocompleteFragment.setPlaceFields(listOf(Place.Field.ID, Place.Field.NAME, Place.Field.LAT_LNG))
        autocompleteFragment.setOnPlaceSelectedListener(object : com.google.android.libraries.places.widget.listener.PlaceSelectionListener {
            override fun onPlaceSelected(place: Place) {
                place.latLng?.let { map?.moveCamera(CameraUpdateFactory.newLatLngZoom(it, 16.0f)) }
            }

            override fun onError(status: com.google.android.gms.common.api.Status) {
                Timber.i("Place selection error: %s", status)
            }
        })

        val mapFragment = supportFragmentManager.findFragmentById(R.id.map) as SupportMapFragment
        mapFragment.getMapAsync(this)

        binding.btnSetLocation.isVisible = !intent.getBooleanExtra("disableSetButton", false)
        binding.btnSetLocation.setOnClickListener {
            val resultIntent = Intent().apply {
                putExtra("latitude", currentPosition.latitude)
                putExtra("longitude", currentPosition.longitude)
            }
            setResult(Activity.RESULT_OK, resultIntent)
            finish()
        }
    }

    override fun onMapReady(googleMap: GoogleMap) {
        map = googleMap

        val fusedLocationClient = LocationServices.getFusedLocationProviderClient(this)
        runCatching {
            fusedLocationClient.lastLocation.addOnSuccessListener { location ->
                if (location != null && currentPosition.latitude == 0.0 && currentPosition.longitude == 0.0) {
                    currentPosition = LatLng(location.latitude, location.longitude)
                }
                googleMap.moveCamera(CameraUpdateFactory.newLatLngZoom(currentPosition, 16.0f))
            }
        }.onFailure { Timber.i(it, "failed to fetch last known location") }

        googleMap.moveCamera(CameraUpdateFactory.newLatLngZoom(currentPosition, 16.0f))
        runCatching { googleMap.isMyLocationEnabled = true }
            .onFailure { Timber.i(it, "location permission not granted") }

        googleMap.setOnCameraIdleListener { currentPosition = googleMap.cameraPosition.target }
        googleMap.setOnCameraMoveListener { currentPosition = googleMap.cameraPosition.target }
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        if (item.itemId == android.R.id.home) {
            onBackPressedDispatcher.onBackPressed()
        }
        return true
    }

    override fun finish() {
        super.finish()
        applyExitTransition(R.anim.slide_in_left, R.anim.slide_out_right)
    }
}
