package com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.transactiondetail
import android.app.Activity
import android.content.Intent
import android.graphics.Paint
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
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.presentation.feature.common.locationpick.LocationPickActivity
import com.nusatim.sapiriku.domain.model.RentVehicleDetail
import com.nusatim.sapiriku.core.common.AppEvent
import com.nusatim.sapiriku.core.common.AppEventBus
import com.nusatim.sapiriku.core.common.Config
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.core.util.ViewUtils
import com.nusatim.sapiriku.core.database.entity.UserEntity
import com.nusatim.sapiriku.databinding.ActivityPartnerRentVehicleTransactionDetailBinding
import com.nusatim.sapiriku.domain.repository.UserRepository
import com.nusatim.sapiriku.presentation.feature.chat.conversation.ChatActivity
import com.nusatim.sapiriku.presentation.feature.customer.detail.CustomerDetailActivity
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.launch
import javax.inject.Inject
import com.nusatim.sapiriku.core.util.applyExitTransition
import com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.reviewcustomer.PartnerReviewCustomerTransactionActivity

@AndroidEntryPoint
class PartnerRentVehicleTransactionDetailActivity : AppCompatActivity() {

    private lateinit var binding: ActivityPartnerRentVehicleTransactionDetailBinding
    private val viewModel: PartnerRentVehicleTransactionDetailViewModel by viewModels()

    @Inject lateinit var appEventBus: AppEventBus
    @Inject lateinit var userRepository: UserRepository

    private var id = 0

    private val feedbackLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) viewModel.loadDetail(id)
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityPartnerRentVehicleTransactionDetailBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        id = intent.getIntExtra("id", 0)

        observeState()
        viewModel.loadDetail(id)
    }

    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)
        setIntent(intent)
        id = intent.getIntExtra("id", 0)
        viewModel.loadDetail(id)
    }

    private fun bindDetail(data: RentVehicleDetail) {
        val customer = data.customerDetail ?: return
        val vehicle = data.vehicle ?: return
        val voucher = data.voucher
        val transactionDetail = data.rentVehicleTransactionDetail ?: return

        binding.txtStatus.text = transactionDetail.statusName
        binding.txtTitle.text = vehicle.title
        binding.txtName.text = "${customer.firstName} ${customer.lastName}"

        binding.imageProfile.load(Config.BASE_CUSTOMER_IMAGE + "thumb_" + customer.imgProfile) {
            placeholder(R.drawable.user_image)
            error(R.drawable.user_image)
        }

        binding.btnCustomerDetail.setOnClickListener {
            val intent = Intent(this, CustomerDetailActivity::class.java).apply {
                putExtra("first_name", customer.firstName)
                putExtra("last_name", customer.lastName)
                putExtra("img_profile", customer.imgProfile)
                putExtra("img_identity", customer.imgIdentity)
                putExtra("identity_number", customer.identityNumber)
                putExtra("phone", customer.phone)
            }
            startActivity(intent)
        }

        if (vehicle.photos.isNotEmpty()) {
            binding.previewVehicle.load(Config.BASE_VEHICLE_IMAGE + "thumb_" + vehicle.photos[0].img) {
                placeholder(R.drawable.no_image)
                error(R.drawable.no_image)
            }
        } else {
            binding.previewVehicle.setImageResource(R.drawable.no_image)
        }
        binding.txtVehicleType.text = vehicle.vehicleTypeName

        val days = ViewUtils.getCountOfDays(
            ViewUtils.mysqlDateToNormalDate(transactionDetail.startDate.orEmpty(), "yyyy-MM-dd HH:mm:ss", "yyyy-MM-dd"),
            ViewUtils.mysqlDateToNormalDate(transactionDetail.endDate.orEmpty(), "yyyy-MM-dd HH:mm:ss", "yyyy-MM-dd")
        ).toIntOrNull() ?: 0
        binding.txtDays.text = days.toString()

        binding.txtPricePackage.text = transactionDetail.pricePackageName
        binding.txtPrice.text = "Rp. ${ViewUtils.formatCurrency(transactionDetail.price)},-"
        binding.txtTotal.text = "Rp. ${ViewUtils.formatCurrency(transactionDetail.price * days)},-"

        binding.cvPick.isVisible = transactionDetail.delivery == 1 || transactionDetail.pickoff == 1

        binding.deliveryContainer.isVisible = transactionDetail.delivery == 1
        if (transactionDetail.delivery == 1) {
            binding.txtDeliveryAddress.text = transactionDetail.deliveryAddress
            binding.txtDeliveryTime.text = ViewUtils.mysqlDateToNormalDate(transactionDetail.deliveryDate.orEmpty(), "yyyy-MM-dd HH:mm:ss", "dd MMM yyyy HH:mm")
            binding.txtDeliveryFee.text = "Rp. ${ViewUtils.formatCurrency(transactionDetail.deliveryFee)},-"

            val hasDeliveryLocation = transactionDetail.deliveryLatitude != 0.0 || transactionDetail.deliveryLongitude != 0.0
            binding.btnShowDeliveryLocation.isVisible = hasDeliveryLocation
            if (hasDeliveryLocation) {
                binding.btnShowDeliveryLocation.setOnClickListener {
                    val intent = Intent(this, LocationPickActivity::class.java).apply {
                        putExtra("latitude", transactionDetail.deliveryLatitude)
                        putExtra("longitude", transactionDetail.deliveryLongitude)
                        putExtra("title", getString(R.string.delivery_location))
                        putExtra("disableSetButton", true)
                    }
                    startActivity(intent)
                    applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
                }
            }
        }

        binding.pickoffContainer.isVisible = transactionDetail.pickoff == 1
        if (transactionDetail.pickoff == 1) {
            binding.txtPickoffAddress.text = transactionDetail.pickoffAddress
            binding.txtPickoffTime.text = ViewUtils.mysqlDateToNormalDate(transactionDetail.pickoffDate.orEmpty(), "yyyy-MM-dd HH:mm:ss", "dd MMM yyyy HH:mm")
            binding.txtPickoffFee.text = "Rp. ${ViewUtils.formatCurrency(transactionDetail.pickoffFee)},-"

            val hasPickoffLocation = transactionDetail.pickoffLatitude != 0.0 || transactionDetail.pickoffLongitude != 0.0
            binding.btnShowPickoffLocation.isVisible = hasPickoffLocation
            if (hasPickoffLocation) {
                binding.btnShowPickoffLocation.setOnClickListener {
                    val intent = Intent(this, LocationPickActivity::class.java).apply {
                        putExtra("latitude", transactionDetail.pickoffLatitude)
                        putExtra("longitude", transactionDetail.pickoffLongitude)
                        putExtra("title", getString(R.string.pickoff_location))
                        putExtra("disableSetButton", true)
                    }
                    startActivity(intent)
                    applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
                }
            }
        }

        binding.cvCouponContainer.isVisible = voucher != null
        if (voucher != null) {
            binding.txtCouponDescription.text = voucher.description
            binding.txtCouponValue.text = "Rp. ${ViewUtils.formatCurrency(voucher.value)},-"

            binding.txtCOuponValidity.isVisible = voucher.useExpire == 1
            if (voucher.useExpire == 1) {
                binding.txtCOuponValidity.text =
                    "${ViewUtils.mysqlDateToNormalDate(voucher.startDate.orEmpty(), "yyyy-MM-dd", "dd MMM yyyy")} - " +
                        ViewUtils.mysqlDateToNormalDate(voucher.endDate.orEmpty(), "yyyy-MM-dd", "dd MMM yyyy")
            }

            binding.txtCouponQuota.isVisible = voucher.useQuota == 1
            if (voucher.useQuota == 1) {
                binding.txtCouponQuota.text = getString(R.string.voucher_quota_left, voucher.quota.toString())
            }

            binding.txtDiscount.text = "Rp. ${ViewUtils.formatCurrency(voucher.value)},-"
        } else {
            binding.txtDiscount.text = "Rp. 0,-"
        }

        when {
            data.hourOvertime > 0 && transactionDetail.status !in intArrayOf(8, 10, 11, 12) -> {
                binding.txtOvertimeInfo.text = "${getString(R.string.overtime)} - ${data.hourOvertime} ${getString(R.string.hours)}"
                binding.txtTotalOvertimeFee.text = "Rp. ${ViewUtils.formatCurrency(data.hourOvertime * transactionDetail.overtimeFee)},-"
            }
            transactionDetail.status == 8 && transactionDetail.overtime == 1 -> {
                binding.txtOvertimeInfo.text = "${getString(R.string.overtime)} - ${transactionDetail.overtimeHour} ${getString(R.string.hours)}"
                binding.txtTotalOvertimeFee.text = "Rp. ${ViewUtils.formatCurrency(transactionDetail.totalOvertimeFee)},-"
            }
            else -> {
                binding.txtOvertimeInfo.text = getString(R.string.overtime)
                binding.txtTotalOvertimeFee.text = "Rp. 0,-"
            }
        }
        binding.txtOvertimeFee.text = "Rp. ${ViewUtils.formatCurrency(transactionDetail.overtimeFee)},-"

        binding.txtTotalPayment.text = "Rp. ${ViewUtils.formatCurrency(transactionDetail.totalPayment)},-"
        binding.txtAdminFee.text = "Rp. ${ViewUtils.formatCurrency(transactionDetail.adminFee)},-"
        binding.txtAdminFee.paintFlags = if (transactionDetail.cashOnDelivery == 1) {
            binding.txtAdminFee.paintFlags or Paint.STRIKE_THRU_TEXT_FLAG
        } else {
            binding.txtAdminFee.paintFlags and Paint.STRIKE_THRU_TEXT_FLAG.inv()
        }

        binding.cvCOD.isVisible = transactionDetail.cashOnDelivery == 1

        binding.btnChat.setOnClickListener {
            lifecycleScope.launch {
                val user = userRepository.getUser().first()
                val intent = Intent(this@PartnerRentVehicleTransactionDetailActivity, ChatActivity::class.java).apply {
                    putExtra("name", "${customer.firstName} ${customer.lastName}")
                    putExtra("image", Config.BASE_CUSTOMER_IMAGE + customer.imgProfile)
                    putExtra("is_partner", true)
                    putExtra("customer_account_id", customer.id)
                    putExtra("partner_account_id", user?.id ?: 0)
                }
                startActivity(intent)
                applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
            }
        }

        binding.btnCancel.setOnClickListener {
            confirm(R.string.cancel_order, R.string.cancel_order_confirmation) {
                viewModel.cancelOrder(id, R.string.cancel_order)
            }
        }
        binding.btnVerify.setOnClickListener {
            confirm(R.string.verify, R.string.verfiy_order_confirmation) {
                viewModel.updateStatus(id, 2, getString(R.string.verify))
            }
        }
        binding.btnDelivery.setOnClickListener {
            confirm(R.string.delivery, R.string.delivery_vehicle_confirmation) {
                viewModel.updateStatus(id, 3, getString(R.string.delivery))
            }
        }
        binding.btnWaitingPickup.setOnClickListener {
            confirm(R.string.waiting_customer_picking_up, R.string.waiting_customer_picking_up_confirmation) {
                viewModel.updateStatus(id, 4, getString(R.string.waiting_customer_picking_up))
            }
        }
        binding.btnPickingUpVehicle.setOnClickListener {
            confirm(R.string.partner_picking_up_vehicle, R.string.partner_picking_up_vehicle_confirmation) {
                viewModel.updateStatus(id, 7, getString(R.string.partner_picking_up_vehicle))
            }
        }
        binding.btnOrderCompleted.setOnClickListener {
            confirm(R.string.order_completed, R.string.order_completed_confirmation) {
                viewModel.completeOrder(id, R.string.cancel_order)
            }
        }
        binding.btnFeedback.setOnClickListener {
            val intent = Intent(this, PartnerReviewCustomerTransactionActivity::class.java)
            intent.putExtra("transaction_id", transactionDetail.id)
            feedbackLauncher.launch(intent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
        }

        updateActionButtons(transactionDetail.status, transactionDetail.delivery, transactionDetail.pickoff, data.feedback)
    }

    private fun updateActionButtons(status: Int, delivery: Int, pickoff: Int, feedback: Int) {
        binding.btnCancel.isVisible = status == 1 || status == 2
        binding.btnVerify.isVisible = status == 1
        binding.btnFeedback.isVisible = status == 8 && feedback == 1
        binding.btnDelivery.isVisible = status == 2 && delivery == 1
        binding.btnWaitingPickup.isVisible = status == 2 && delivery != 1
        binding.btnPickingUpVehicle.isVisible = status == 6 && pickoff == 1
        binding.btnOrderCompleted.isVisible = (status == 6 && pickoff != 1) || status == 7
    }

    private fun confirm(titleResId: Int, messageResId: Int, onConfirm: () -> Unit) {
        AlertDialog.Builder(this)
            .setTitle(getString(titleResId))
            .setMessage(getString(messageResId))
            .setPositiveButton(R.string.yes) { _, _ -> onConfirm() }
            .setNegativeButton(R.string.no, null)
            .show()
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch { viewModel.detail.collect { state -> handleDetailState(state) } }
                launch { viewModel.actionResult.collect { result -> handleActionResult(result) } }
                launch {
                    appEventBus.events.collect { event ->
                        if (event is AppEvent.PartnerRentVehicleTransactionUpdated && event.id == id) {
                            viewModel.loadDetail(id)
                        }
                    }
                }
            }
        }
    }

    private fun handleDetailState(state: UiState<RentVehicleDetail>) {
        when (state) {
            is UiState.Success -> bindDetail(state.data)
            is UiState.Error -> Toast.makeText(this, getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
            else -> Unit
        }
    }

    private fun handleActionResult(result: PartnerTransactionActionResult?) {
        if (result == null) return
        val title = result.title ?: result.titleResId?.let { getString(it) } ?: ""
        AlertDialog.Builder(this)
            .setTitle(title)
            .setMessage(result.message)
            .setPositiveButton(R.string.yes) { _, _ ->
                viewModel.consumeActionResult()
                if (result.success) {
                    setResult(Activity.RESULT_OK)
                    if (result.isComplete) {
                        val intent = Intent(this, PartnerReviewCustomerTransactionActivity::class.java)
                        intent.putExtra("transaction_id", id)
                        feedbackLauncher.launch(intent)
                        finish()
                        applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
                    } else {
                        viewModel.loadDetail(id)
                    }
                }
            }
            .show()
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
