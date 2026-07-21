package com.rentone.user.presentation.feature.rentvehicle.selectregency
import android.app.Activity
import android.content.Intent
import android.os.Bundle
import android.view.MenuItem
import android.widget.ArrayAdapter
import android.widget.ListView
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.isVisible
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.rentone.user.R
import com.rentone.user.core.common.UiState
import com.rentone.user.core.util.getSerializableExtraCompat
import com.rentone.user.databinding.ActivityRentVehicleSelectRegencyBinding
import com.rentone.user.domain.model.BasicData
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.rentone.user.core.util.applyExitTransition
import com.rentone.user.presentation.feature.rentvehicle.listvehicle.RentVehicleListVehicleActivity
import com.rentone.user.presentation.feature.rentvehicle.datepicker.RentVehicleDatePickerActivity

@AndroidEntryPoint
class RentVehicleSelectRegencyActivity : AppCompatActivity() {

    private lateinit var binding: ActivityRentVehicleSelectRegencyBinding
    private val viewModel: RentVehicleSelectRegencyViewModel by viewModels()

    private val param: HashMap<String, String> by lazy {
        intent.getSerializableExtraCompat<HashMap<String, String>>("param") ?: HashMap()
    }

    private var regencies: List<BasicData> = emptyList()

    private val datePickerLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            val intent = Intent(this, RentVehicleListVehicleActivity::class.java).apply {
                flags = Intent.FLAG_ACTIVITY_FORWARD_RESULT
                putExtra("param", result.data?.getSerializableExtraCompat<HashMap<String, String>>("param"))
            }
            startActivity(intent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityRentVehicleSelectRegencyBinding.inflate(layoutInflater)
        setContentView(binding.root)

        instance = this

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        observeState()
        viewModel.loadRegencies()
    }

    private fun setupList(data: List<BasicData>) {
        regencies = data
        val adapter = ArrayAdapter(this, android.R.layout.simple_list_item_1, data.map { it.name })
        binding.list.choiceMode = ListView.CHOICE_MODE_SINGLE
        binding.list.adapter = adapter
        binding.list.setOnItemClickListener { _, _, position, _ ->
            val selected = regencies[position]
            param["regency"] = selected.id.toString()
            param["regency_name"] = selected.name
            val intent = Intent(this, RentVehicleDatePickerActivity::class.java).apply {
                putExtra("param", param)
            }
            datePickerLauncher.launch(intent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
        }
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.regencies.collect { state -> handleUiState(state) }
            }
        }
    }

    private fun handleUiState(state: UiState<List<BasicData>>) {
        when (state) {
            is UiState.Success -> {
                if (state.data.isNotEmpty()) {
                    setupList(state.data)
                    binding.txtListMessage.isVisible = false
                    binding.list.isVisible = true
                } else {
                    binding.txtListMessage.isVisible = true
                    binding.list.isVisible = false
                }
            }
            is UiState.Error -> {
                AlertDialog.Builder(this)
                    .setTitle(title)
                    .setMessage(getString(R.string.failed_check_to_server))
                    .setPositiveButton(R.string.yes) { _, _ -> finish() }
                    .show()
            }
            else -> Unit
        }
    }

    override fun finish() {
        super.finish()
        applyExitTransition(R.anim.slide_in_left, R.anim.slide_out_right)
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        if (item.itemId == android.R.id.home) {
            onBackPressedDispatcher.onBackPressed()
        }
        return true
    }

    companion object {
        var instance: RentVehicleSelectRegencyActivity? = null
    }
}
