package com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.listvehicle
import android.app.Activity
import android.content.Intent
import android.os.Bundle
import android.view.Menu
import android.view.MenuItem
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.isVisible
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.recyclerview.widget.LinearLayoutManager
import android.widget.Toast
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.presentation.feature.common.listsort.ListSortActivity
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.core.util.MenuUtils
import com.nusatim.sapiriku.core.util.getSerializableExtraCompat
import com.nusatim.sapiriku.custom.ArrayAdapterWithIcon
import com.nusatim.sapiriku.custom.PaginationListener
import com.nusatim.sapiriku.databinding.ActivityPartnerListRentVehicleBinding
import com.nusatim.sapiriku.domain.model.BasicData
import com.nusatim.sapiriku.domain.model.FilterList
import com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.listvehicle.adapter.PartnerListVehicleAdapter
import com.nusatim.sapiriku.presentation.feature.filter.ListFilterActivity
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.nusatim.sapiriku.core.util.applyExitTransition
import com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.itemdetail.PartnerRentVehicleItemDetailActivity
import com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.addvehicle.PartnerAddVehicleActivity
import com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.config.PartnerRentVehicleConfigActivity

@AndroidEntryPoint
class PartnerListRentVehicleActivity : AppCompatActivity() {

    private lateinit var binding: ActivityPartnerListRentVehicleBinding
    private val viewModel: PartnerListRentVehicleViewModel by viewModels()
    private lateinit var adapter: PartnerListVehicleAdapter

    private val filterLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            val filter = result.data?.getSerializableExtraCompat<FilterList>("filter")
            if (filter != null) viewModel.updateFilter(filter)
        }
    }

    private val sortLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            viewModel.updateSort(result.data?.getIntExtra("sort", 0) ?: 0)
        }
    }

    private val addVehicleLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) viewModel.loadFirstPage()
    }

    private val configLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) viewModel.loadFirstPage()
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityPartnerListRentVehicleBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        setupList()
        setupListeners()
        observeState()
        viewModel.loadFirstPage()
    }

    private fun setupList() {
        adapter = PartnerListVehicleAdapter(
            onItemClick = { vehicle ->
                val intent = Intent(this, PartnerRentVehicleItemDetailActivity::class.java)
                intent.putExtra("id", vehicle.id)
                startActivity(intent)
                applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
            }
        )

        val layoutManager = LinearLayoutManager(this)
        binding.list.layoutManager = layoutManager
        binding.list.adapter = adapter
        binding.list.addOnScrollListener(object : PaginationListener(layoutManager) {
            override fun loadMoreItems() = viewModel.loadMore()
            override fun isLastPage() = viewModel.uiState.value.isLastPage
            override fun isLoading() = viewModel.uiState.value.isLoadingMore
        })
        binding.srLayout.setOnRefreshListener { viewModel.loadFirstPage(isRefresh = true) }
    }

    private fun setupListeners() {
        binding.filterMenu.setOnClickListener {
            val intent = Intent(this, ListFilterActivity::class.java).apply {
                putExtra("filter", viewModel.uiState.value.filterList)
                putExtra("status", true)
                putExtra("maxPassenger", true)
                putExtra("priceRange", true)
                putExtra("vehicleFunctionalType", true)
            }
            filterLauncher.launch(intent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
        }

        binding.sortMenu.setOnClickListener {
            val items = arrayOf(
                getString(R.string.last_update),
                getString(R.string.alfabetic_asc),
                getString(R.string.alfabetic_desc),
                getString(R.string.price_high),
                getString(R.string.price_low)
            )
            val intent = Intent(this, ListSortActivity::class.java).apply {
                putExtra("list_sort", items)
                putExtra("sort", viewModel.sortIndex)
            }
            sortLauncher.launch(intent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
        }
    }

    private fun openSelectorAddVehicle(types: List<BasicData>) {
        val adapter = ArrayAdapterWithIcon(this, ArrayList(types))
        MenuUtils.buildPopupList(this, getString(R.string.select_type), R.drawable.ic_car, adapter, { _, which ->
            val data = adapter.getItem(which) ?: return@buildPopupList
            val intent = Intent(this, PartnerAddVehicleActivity::class.java)
            intent.putExtra("functional_type", data.id)
            addVehicleLauncher.launch(intent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
        })
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.uiState.collect { state ->
                        binding.srLayout.isRefreshing = state.isRefreshing
                        adapter.setLoading(state.isLoadingMore)
                        adapter.submitList(state.vehicles)

                        if (state.isInitialLoading) {
                            binding.shimmer.isVisible = true
                            binding.shimmer.startShimmer()
                        } else {
                            binding.shimmer.stopShimmer()
                            binding.shimmer.isVisible = false
                        }

                        val isEmpty = state.vehicles.isEmpty() && !state.isInitialLoading
                        binding.list.isVisible = !isEmpty
                        binding.txtListMessage.isVisible = isEmpty

                        if (state.error != null) {
                            Toast.makeText(this@PartnerListRentVehicleActivity, getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
                        }
                    }
                }
                launch {
                    viewModel.functionalType.collect { state ->
                        when (state) {
                            is UiState.Success -> openSelectorAddVehicle(state.data)
                            is UiState.Error -> {
                                AlertDialog.Builder(this@PartnerListRentVehicleActivity)
                                    .setTitle(getString(R.string.add_vehicle))
                                    .setMessage(getString(R.string.failed_check_to_server))
                                    .setPositiveButton(R.string.yes, null)
                                    .show()
                            }
                            else -> Unit
                        }
                    }
                }
            }
        }
    }

    override fun onCreateOptionsMenu(menu: Menu): Boolean {
        menuInflater.inflate(R.menu.basic_list, menu)
        return super.onCreateOptionsMenu(menu)
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        when (item.itemId) {
            android.R.id.home -> onBackPressedDispatcher.onBackPressed()
            R.id.action_add -> viewModel.loadFunctionalTypes()
            R.id.action_config -> {
                val intent = Intent(this, PartnerRentVehicleConfigActivity::class.java)
                configLauncher.launch(intent)
                applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
            }
        }
        return true
    }

    override fun finish() {
        super.finish()
        applyExitTransition(R.anim.slide_in_left, R.anim.slide_out_right)
    }
}
