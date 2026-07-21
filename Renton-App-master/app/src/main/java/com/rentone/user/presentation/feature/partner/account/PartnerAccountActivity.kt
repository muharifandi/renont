package com.rentone.user.presentation.feature.partner.account
import android.app.Activity
import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.view.MenuItem
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.isVisible
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import coil.load
import com.google.android.gms.maps.CameraUpdateFactory
import com.google.android.gms.maps.GoogleMap
import com.google.android.gms.maps.SupportMapFragment
import com.google.android.gms.maps.model.LatLng
import com.rentone.user.R
import com.rentone.user.presentation.feature.common.locationpick.LocationPickActivity
import com.rentone.user.presentation.feature.common.selectregency.SelectRegencyActivity
import com.rentone.user.domain.model.PartnerAccountDetail
import com.rentone.user.core.common.Config
import com.rentone.user.core.common.UiState
import com.rentone.user.databinding.ActivityPartnerAccountBinding
import com.rentone.user.presentation.feature.partner.account.adapter.ArrayFeatureAdapter
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.rentone.user.core.util.applyExitTransition
import com.rentone.user.presentation.feature.partner.changecompanyname.PartnerChangeCompanyNameActivity
import com.rentone.user.presentation.feature.partner.changedescription.PartnerChangeDescriptionActivity
import com.rentone.user.presentation.feature.partner.changeaddress.PartnerChangeAddressActivity

@AndroidEntryPoint
class PartnerAccountActivity : AppCompatActivity() {

    private lateinit var binding: ActivityPartnerAccountBinding
    private val viewModel: PartnerAccountViewModel by viewModels()

    private var partnerDetail: PartnerAccountDetail? = null

    private val pickImageLauncher = registerForActivityResult(ActivityResultContracts.GetContent()) { uri: Uri? ->
        uri?.let {
            binding.profileImage.load(it) { error(R.drawable.user_image) }
            viewModel.uploadProfileImage(it)
        }
    }

    private val editFieldLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) viewModel.loadDetail()
    }

    private val selectRegencyLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            val regenciesId = result.data?.getIntExtra("regencies_id", 0) ?: 0
            viewModel.changeRegency(regenciesId)
        }
    }

    private val pickLocationLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            val latitude = result.data?.getDoubleExtra("latitude", 0.0) ?: 0.0
            val longitude = result.data?.getDoubleExtra("longitude", 0.0) ?: 0.0
            viewModel.changeLocation(latitude, longitude)
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityPartnerAccountBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        binding.srLayout.setOnRefreshListener { viewModel.loadDetail() }

        observeState()
        viewModel.loadDetail()
    }

    private fun bindDetail(data: PartnerAccountDetail) {
        partnerDetail = data
        binding.srLayout.isRefreshing = false
        val partner = data.partnerDetail ?: return

        binding.profileImage.load(Config.BASE_PARTNER_IMAGE + "thumb_" + partner.imgProfile) {
            error(R.drawable.user_image)
        }
        binding.profileImage.setOnClickListener { pickImageLauncher.launch("image/*") }

        binding.txtName.text = partner.companyName
        binding.imgCompanyNameEdit.setOnClickListener {
            val intent = Intent(this, PartnerChangeCompanyNameActivity::class.java)
            intent.putExtra("company_name", partner.companyName)
            editFieldLauncher.launch(intent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
        }

        binding.txtOwnership.text = partner.ownershipName

        binding.txtDescription.text = partner.description
        binding.imgDescriptionEdit.setOnClickListener {
            val intent = Intent(this, PartnerChangeDescriptionActivity::class.java)
            intent.putExtra("description", partner.description)
            editFieldLauncher.launch(intent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
        }

        binding.txtRegency.text = partner.regenciesName
        binding.imgRegencyEdit.setOnClickListener {
            val intent = Intent(this, SelectRegencyActivity::class.java)
            intent.putExtra("regency_name", partner.regenciesName)
            selectRegencyLauncher.launch(intent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
        }

        binding.txtAddress.text = partner.address
        binding.imgAddressEdit.setOnClickListener {
            val intent = Intent(this, PartnerChangeAddressActivity::class.java)
            intent.putExtra("address", partner.address)
            editFieldLauncher.launch(intent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
        }

        val mapFragment = supportFragmentManager.findFragmentById(R.id.map) as? SupportMapFragment
        mapFragment?.getMapAsync { googleMap: GoogleMap ->
            val position = LatLng(partner.latitude, partner.longitude)
            googleMap.moveCamera(CameraUpdateFactory.newLatLngZoom(position, 16.0f))
            googleMap.uiSettings.setScrollGesturesEnabled(false)
        }

        binding.btnSetLocation.setOnClickListener {
            val intent = Intent(this, LocationPickActivity::class.java)
            intent.putExtra("latitude", partner.latitude)
            intent.putExtra("longitude", partner.longitude)
            pickLocationLauncher.launch(intent)
        }

        val featureAdapter = ArrayFeatureAdapter(this, ArrayList(data.partnerFeatures)) { featureId ->
            viewModel.requestFeature(featureId)
        }
        binding.lvFeature.adapter = featureAdapter
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.detail.collect { state -> handleDetailState(state) }
                }
                launch {
                    viewModel.actionResult.collect { result ->
                        if (result != null) {
                            AlertDialog.Builder(this@PartnerAccountActivity)
                                .setTitle(getString(result.titleResId))
                                .setMessage(result.message ?: getString(R.string.failed_check_to_server))
                                .setPositiveButton(R.string.yes) { _, _ ->
                                    viewModel.consumeActionResult()
                                    if (result.success) viewModel.loadDetail()
                                }
                                .setOnCancelListener { viewModel.consumeActionResult() }
                                .show()
                        }
                    }
                }
            }
        }
    }

    private fun handleDetailState(state: UiState<PartnerAccountDetail>) {
        when (state) {
            is UiState.Loading -> {
                binding.mainContainer.isVisible = false
                binding.mainShimmer.isVisible = true
                binding.mainShimmer.startShimmer()
            }
            is UiState.Success -> {
                binding.mainShimmer.stopShimmer()
                binding.mainShimmer.isVisible = false
                binding.mainContainer.isVisible = true
                bindDetail(state.data)
            }
            is UiState.Error -> {
                binding.srLayout.isRefreshing = false
                Toast.makeText(this, getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
            }
            else -> Unit
        }
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
