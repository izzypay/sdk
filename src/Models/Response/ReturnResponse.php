<?php

declare(strict_types=1);

namespace IzzyPay\Models\Response;

class ReturnResponse
{
    private string $returnDate;
    private ?float $reducedValue;

    /**
     * @param string $returnDate
     * @param float|null $reducedValue
     */
    public function __construct(string $returnDate, ?float $reducedValue)
    {
        $this->returnDate = $returnDate;
        $this->reducedValue = $reducedValue;
    }

    /**
     * @return string
     */
    public function getReturnDate(): string
    {
        return $this->returnDate;
    }

    /**
     * @return float|null
     */
    public function getReducedValue(): ?float
    {
        return $this->reducedValue;
    }
}
