package com.nusatim.sapiriku.presentation.feature.rentvehicle.itemdetail
import android.app.Activity
import android.content.Intent
import android.graphics.BitmapFactory
import android.graphics.Color
import android.graphics.drawable.ColorDrawable
import android.os.Bundle
import android.util.Base64
import android.view.MenuItem
import android.view.View
import android.widget.ArrayAdapter
import android.widget.ImageView
import android.widget.TextView
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import androidx.core.view.isVisible
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.recyclerview.widget.LinearLayoutManager
import coil.load
import com.google.android.gms.maps.CameraUpdateFactory
import com.google.android.gms.maps.GoogleMap
import com.google.android.gms.maps.SupportMapFragment
import com.google.android.gms.maps.model.LatLng
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.presentation.feature.common.zoomimage.ZoomImageListActivity
import com.nusatim.sapiriku.domain.model.VehicleDetail
import com.nusatim.sapiriku.core.common.Config
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.core.util.MenuUtils
import com.nusatim.sapiriku.core.util.ViewUtils
import com.nusatim.sapiriku.core.util.getSerializableExtraCompat
import com.nusatim.sapiriku.databinding.ActivityVehicleItemDetailBinding
import com.nusatim.sapiriku.domain.model.DateRange
import com.nusatim.sapiriku.domain.model.PackagePrice
import com.nusatim.sapiriku.domain.model.PartnerDetail
import com.nusatim.sapiriku.domain.model.Vehicle
import com.nusatim.sapiriku.presentation.feature.chat.conversation.ChatActivity
import com.nusatim.sapiriku.presentation.feature.rentvehicle.adapter.ListVehicleReviewAdapter
import com.nusatim.sapiriku.presentation.feature.login.LoginActivity
import com.nusatim.sapiriku.core.ui.ImagePagerAdapter
import com.savvi.rangedatepicker.CalendarPickerView
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import java.text.SimpleDateFormat
import java.util.Calendar
import java.util.Date
import java.util.Locale
import com.nusatim.sapiriku.core.util.applyExitTransition
import com.nusatim.sapiriku.presentation.feature.rentvehicle.listreview.RentVehicleListReviewActivity
import com.nusatim.sapiriku.presentation.feature.rentvehicle.checkout.RentVehicleOrderCheckoutActivity
import com.nusatim.sapiriku.presentation.feature.rentvehicle.datepicker.RentVehicleDatePickerActivity

@AndroidEntryPoint
class VehicleItemDetailActivity : AppCompatActivity() {

    private lateinit var binding: ActivityVehicleItemDetailBinding
    private val viewModel: VehicleItemDetailViewModel by viewModels()

    private var id = 0
    private var vehicle: Vehicle? = null
    private var partner: PartnerDetail? = null
    private var datesBooked: List<DateRange> = emptyList()
    private var detailResponse: VehicleDetail? = null
    private var pricePackage = 0
    private var dateSelected = false
    private var currentUserId: Int? = null

    @Suppress("UNCHECKED_CAST")
    private var param: HashMap<String, String> = HashMap()

    private val loginLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            checkoutOrder()
        }
    }

    private val datePickerLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            val newParam = result.data?.getSerializableExtraCompat<HashMap<String, String>>("param")
            if (newParam != null) {
                param = newParam
                detailResponse?.let { bindDetail(it) }
            }
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityVehicleItemDetailBinding.inflate(layoutInflater)
        setContentView(binding.root)

        instance = this

        supportActionBar?.setDisplayHomeAsUpEnabled(true)

        id = intent.getIntExtra("id", 0)
        param = intent.getSerializableExtraCompat<HashMap<String, String>>("param") ?: HashMap()

        val minDate = Calendar.getInstance()
        val maxDate = Calendar.getInstance().apply { add(Calendar.MONTH, 1) }
        binding.calendarView.init(minDate.time, maxDate.time, SimpleDateFormat("dd MMM yyyy", Locale.getDefault()))
            .inMode(CalendarPickerView.SelectionMode.RANGE)

        observeState()
        viewModel.loadDetail(id)
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.detail.collect { state -> handleDetailState(state) }
                }
                launch {
                    viewModel.currentUser.collect { user -> currentUserId = user?.id }
                }
            }
        }
    }

    private fun handleDetailState(state: UiState<VehicleDetail>) {
        when (state) {
            is UiState.Success -> {
                vehicle = state.data.vehicle
                partner = state.data.partner
                datesBooked = state.data.vehicleBooked
                detailResponse = state.data
                bindDetail(state.data)
            }
            is UiState.Error -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.menu_rent_vehicle))
                    .setMessage(getString(R.string.failed_check_to_server))
                    .setPositiveButton(R.string.yes) { _, _ -> finish() }
                    .show()
            }
            else -> Unit
        }
    }

    private fun bindDetail(data: VehicleDetail) {
        val vehicle = data.vehicle
        this.vehicle = vehicle
        val partner = data.partner

        dateSelected = param["start_date"] != null || param["end_date"] != null

        title = vehicle.title

        binding.carouselView.isVisible = vehicle.photos.isNotEmpty()
        binding.carouselView.adapter = ImagePagerAdapter(
            urls = vehicle.photos.map { Config.BASE_VEHICLE_IMAGE + it.img },
            placeholder = R.drawable.ic_time,
            errorDrawable = R.drawable.ic_broken_image
        ) { position ->
            val zoomIntent = Intent(this, ZoomImageListActivity::class.java)
            zoomIntent.putExtra("photo", Config.BASE_VEHICLE_IMAGE + vehicle.photos[position].img)
            startActivity(zoomIntent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
        }

        binding.btnPackage.setOnClickListener { openSelectorPackagePrice(vehicle, data.forceWithDriver) }
        binding.txtTitle.text = vehicle.title

        bindIconField(binding.txtVehicleType, binding.iconVehicleType, vehicle.vehicleTypeName, vehicle.vehicleTypeIcon, R.string.vehicle_type)
        bindIconField(binding.txtBrand, binding.iconBrand, vehicle.brandName, vehicle.brandIcon, R.string.brand)
        bindIconField(binding.txtVehicleModel, binding.iconVehicleModel, vehicle.vehicleModelName, vehicle.vehicleModelIcon, R.string.model)
        bindIconField(binding.txtTransmition, binding.iconTransmition, vehicle.transmitionTypeName, vehicle.transmitionTypeIcon, R.string.transmition)
        bindIconField(binding.txtDrivenType, binding.iconDrivenType, vehicle.drivenTypeName, vehicle.drivenTypeIcon, R.string.driven)
        bindIconField(binding.txtFuel, binding.iconFuel, vehicle.fuelTypeName, vehicle.fuelTypeIcon, R.string.driven)

        binding.txtMaxPassenger.text = if (vehicle.maxPassenger > 0) vehicle.maxPassenger.toString() else "N/A"
        binding.txtMaxBaggage.text = if (vehicle.maxBaggage > 0) vehicle.maxBaggage.toString() else "N/A"
        binding.txtYear.text = vehicle.year.toString()

        if (vehicle.colorName != null) {
            binding.txtColor.text = vehicle.colorName
            binding.colorBox.isVisible = vehicle.colorValue != null
            vehicle.colorValue?.let { binding.colorBox.setImageDrawable(ColorDrawable(Color.parseColor(it))) }
        } else {
            binding.colorBox.isVisible = false
            binding.txtColor.text = "${getString(R.string.color)}\n\nN/A"
        }

        if (vehicle.withDriver == 1) pricePackage = 1
        setPrice(vehicle)

        binding.txtWithDriver.text = getString(
            if (vehicle.withDriver == 0) R.string.optional_if_available else R.string.must_with_driver
        )

        binding.txtPartnerTitle.text = partner?.companyName
        binding.imageProfile.load(Config.BASE_PARTNER_IMAGE + "thumb_" + partner?.imgProfile) {
            placeholder(R.drawable.no_image)
            error(R.drawable.no_image)
        }
        binding.txtPartnerRegency.text = partner?.regenciesName
        binding.txtInfo.text = partner?.description
        binding.txtPartnerAddress.text = partner?.address

        val mapFragment = supportFragmentManager.findFragmentById(R.id.map) as? SupportMapFragment
        mapFragment?.getMapAsync { googleMap: GoogleMap ->
            val position = LatLng(partner?.latitude ?: 0.0, partner?.longitude ?: 0.0)
            googleMap.moveCamera(CameraUpdateFactory.newLatLngZoom(position, 16.0f))
            googleMap.uiSettings.setScrollGesturesEnabled(false)
        }

        binding.btnChat.isVisible = currentUserId != null && partner?.accountId != currentUserId
        binding.btnChat.setOnClickListener { onChatClicked(vehicle, partner) }

        binding.btnOrder.setOnClickListener { checkoutOrder() }

        val listDateBooked = datesBooked.flatMap { range ->
            ViewUtils.getDates(range.startDate.orEmpty(), range.endDate.orEmpty())
        }
        binding.calendarView.highlightDates(listDateBooked)
        binding.calendarView.setSelected(false)

        val layoutManager = LinearLayoutManager(this)
        binding.list.layoutManager = layoutManager

        binding.txtReviews.text = "${getString(R.string.reviews)} (${data.reviewTotal})"
        if (data.reviews.isNotEmpty()) {
            binding.reviewContainer.isVisible = true
            binding.txtReviewMessage.isVisible = false
            val reviewAdapter = ListVehicleReviewAdapter()
            binding.list.adapter = reviewAdapter
            reviewAdapter.submitList(data.reviews)

            binding.btnReview.setOnClickListener {
                val intent = Intent(this, RentVehicleListReviewActivity::class.java)
                intent.putExtra("id", vehicle.id)
                startActivity(intent)
                applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
            }
        } else {
            binding.reviewContainer.isVisible = false
            binding.txtReviewMessage.isVisible = true
        }
    }

    private fun bindIconField(text: TextView, icon: ImageView, name: String?, iconBase64: String?, fallbackLabelResId: Int) {
        if (name != null) {
            text.text = name
            icon.setImageBitmap(null)
            if (iconBase64 != null) {
                icon.isVisible = true
                val decodedString = Base64.decode(iconBase64, Base64.DEFAULT)
                val decodedByte = BitmapFactory.decodeByteArray(decodedString, 0, decodedString.size)
                icon.setImageBitmap(decodedByte)
                icon.setColorFilter(ContextCompat.getColor(this, R.color.colorAccent))
            } else {
                icon.isVisible = false
            }
        } else {
            icon.isVisible = false
            text.text = "${getString(fallbackLabelResId)}\n\nN/A"
        }
    }

    private fun openSelectorPackagePrice(vehicle: Vehicle, forceWithDriver: Int) {
        val data = mutableListOf<PackagePrice>()

        if (vehicle.withDriver == 0) {
            data.add(PackagePrice(0, "${getString(R.string.car_only)} - Rp. ${ViewUtils.formatCurrency(vehicle.price)},-"))
        }
        if (forceWithDriver == 0) {
            if (vehicle.priceWithDriverBasic > 0.0) {
                data.add(PackagePrice(1, "${getString(R.string.car_plus_driver_basic)} - Rp. ${ViewUtils.formatCurrency(vehicle.priceWithDriverBasic)},-"))
            }
            if (vehicle.priceWithDriverFull > 0.0) {
                data.add(PackagePrice(2, "${getString(R.string.car_plus_driver_all_in)} - Rp. ${ViewUtils.formatCurrency(vehicle.priceWithDriverFull)},-"))
            }
        }

        val adapter = ArrayAdapter(this, android.R.layout.simple_list_item_1, data)
        MenuUtils.buildPopupList(this, getString(R.string.select_package), R.drawable.ic_car, adapter, { _, which ->
            pricePackage = data[which].pricePackage
            setPrice(vehicle)
        })
    }

    private fun setPrice(vehicle: Vehicle) {
        val (selectedPrice, stringId) = when (pricePackage) {
            1 -> vehicle.priceWithDriverBasic to R.string.car_plus_driver_basic
            2 -> vehicle.priceWithDriverFull to R.string.car_plus_driver_all_in
            else -> vehicle.price to R.string.car_only
        }
        binding.txtPrice.text = "Rp. ${ViewUtils.formatCurrency(selectedPrice)},-"

        if (dateSelected) {
            binding.checkoutInfoContainer.isVisible = true
            val days = ViewUtils.getCountOfDays(param["start_date"].orEmpty(), param["end_date"].orEmpty()).toIntOrNull() ?: 0
            binding.txtTotal.text = "Rp. ${ViewUtils.formatCurrency(selectedPrice * days)},-"
            binding.txtOrderDate.text =
                "${ViewUtils.mysqlDateToNormalDate(param["start_date"].orEmpty(), "yyyy-MM-dd", "dd MMM yyyy")} - " +
                    "${ViewUtils.mysqlDateToNormalDate(param["end_date"].orEmpty(), "yyyy-MM-dd", "dd MMM yyyy")} , $days ${getString(R.string.days)}"
        } else {
            binding.checkoutInfoContainer.isVisible = false
        }

        binding.btnPackage.text = getString(stringId)
    }

    private fun onChatClicked(vehicle: Vehicle, partner: PartnerDetail?) {
        val userId = currentUserId
        if (userId == null) {
            loginLauncher.launch(Intent(this, LoginActivity::class.java))
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
            return
        }

        val (selectedPrice, _) = when (pricePackage) {
            1 -> vehicle.priceWithDriverBasic to R.string.car_plus_driver_basic
            2 -> vehicle.priceWithDriverFull to R.string.car_plus_driver_all_in
            else -> vehicle.price to R.string.car_only
        }

        val intent = Intent(this, ChatActivity::class.java).apply {
            putExtra("name", partner?.companyName)
            putExtra("image", Config.BASE_PARTNER_IMAGE + partner?.imgProfile)
            putExtra("partner_account_id", partner?.accountId ?: 0)
            putExtra("customer_account_id", userId)
            putExtra("attachment_type", 4)
            putExtra("vehicle_id", vehicle.id)
            putExtra("vehicle_title", vehicle.title)
            if (vehicle.photos.isNotEmpty()) putExtra("vehicle_img", vehicle.photos[0].img)
            putExtra("vehicle_type", vehicle.vehicleTypeName)
            putExtra("vehicle_regencies", vehicle.regenciesName)
            putExtra("vehicle_price", selectedPrice)
        }
        startActivity(intent)
        applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
    }

    private fun checkoutOrder() {
        val userId = currentUserId
        val vehicle = this.vehicle ?: return

        if (userId == null) {
            loginLauncher.launch(Intent(this, LoginActivity::class.java))
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
            return
        }

        if (dateSelected) {
            val intent = Intent(this, RentVehicleOrderCheckoutActivity::class.java).apply {
                flags = Intent.FLAG_ACTIVITY_FORWARD_RESULT
                putExtra("package", pricePackage)
                putExtra("vehicle_id", vehicle.id)
                putExtra("param", param)
            }
            startActivity(intent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
        } else {
            val intent = Intent(this, RentVehicleDatePickerActivity::class.java)
            if (datesBooked.isNotEmpty()) {
                intent.putExtra("dates_booked", ArrayList(datesBooked))
            }
            datePickerLauncher.launch(intent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
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

    companion object {
        var instance: VehicleItemDetailActivity? = null
    }
}
